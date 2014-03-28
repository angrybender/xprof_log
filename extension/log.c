#include <time.h>
#include <sys/time.h>

static char *addslashes(char *str_buff);
static void save_log(char *str_buff, int indent);
static void set_log_path(char *path);
static void dump_superglobal();
static void save_func_call(char *func_name, char *file_name, int line);
static void save_called_func_arg(zval *element);
static void set_start();
static void log_end();

// ====================================================================================

static char *addslashes(char *str_buff) {
    zval *args[2];
    zend_uint param_count = 1;
    zval retval_ptr;

    zval function_name;
    zval text;
    INIT_ZVAL(function_name);
    INIT_ZVAL(text);

    ZVAL_STRING(&function_name, "htmlspecialchars", 1);
    ZVAL_STRING(&text, str_buff, 1);
    args[0] = &text;

    if (call_user_function(
            CG(function_table), NULL, &function_name,
            &retval_ptr, param_count, args TSRMLS_CC
        ) == SUCCESS
    ) {
        zval_dtor(&function_name);
        return Z_STRVAL(retval_ptr);
    }
    else {
        zval_dtor(&function_name);
        return "<ERROR_PARSE_PARAM type=\"addslashes\" />";
    }
}

char *_log_path;
char _log_file_name[150] = {0};
static int _is_start = 1;

static void save_log(char *str_buff, int indent) {
    //php_stream *stream;
    FILE        *file;
    int         i, len;
    char        *file_path;

    indent++;
    if (_is_start == 1) {
        _is_start = 0;
        _set_log_file_name();
        save_log("<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>", -1);
        save_log("<ROOT>", -1);
    }

    len = strlen(_log_path) + strlen(_log_file_name) + 1;
    file_path = (char *)emalloc(len);
    snprintf(file_path, len, "%s%s", _log_path, _log_file_name);

    file = fopen(file_path, "a+");

    // отступы для красоты
    for (i = 0; i < indent; i++) {
        fwrite("\t", 1, 1, file);
    }

    fwrite(str_buff, 1, strlen(str_buff), file);
    fwrite("\n", 1, 1, file);

    /*stream = php_stream_open_wrapper(file_path, "a+", REPORT_ERRORS, NULL);

    if (stream) {

        // отступы для красоты
        for (i = 0; i < indent; i++) {
            php_stream_write(stream, "\t", 1);
        }
        php_stream_write(stream, str_buff, strlen(str_buff));
        php_stream_write(stream, "\n", 1);

        php_stream_close(stream);
    }*/

    efree(file_path);
    fclose(file);
}

static char *_var_export(zval *element) {

    zval *args[2];
    zend_uint param_count = 2;
    zval retval_ptr;

    args[0] = element;

    zval function_name;
    zval export_flag;
    INIT_ZVAL(function_name);
    INIT_ZVAL(export_flag);

    ZVAL_STRING(&function_name, "var_export", 1);
    ZVAL_BOOL(&export_flag, 1);
    args[1] = &export_flag;

    if (call_user_function(
            CG(function_table), NULL, &function_name,
            &retval_ptr, param_count, args TSRMLS_CC
        ) == SUCCESS
    ) {

        zval_dtor(&function_name);
        return addslashes(Z_STRVAL(retval_ptr));
    }
    else {
        zval_dtor(&function_name);
        return "<ERROR_PARSE_PARAM type=\"var_export\" />";
    }
}

static int is_first_dump = 1;
static void _dump_superglobal(char *name) {
    zend_auto_global    *auto_global;
    char                *tag_name_open;
    char                *tag_name_close;
    int                 len;
    char                *dump_string;

    len = strlen(name) + 4;
    tag_name_open = (char*)emalloc(len);
    snprintf(tag_name_open, len, "<%s>", name);

    tag_name_close = (char*)emalloc(len);
    snprintf(tag_name_close, len, "</%s>", name);

    save_log(tag_name_open, 1);

    // This code makes sure $_{name} has been initialized - sall segfault on SESSION when not sess_start
	/*if (!zend_hash_exists(&EG(symbol_table), name, strlen(name) + 1)) {
        zend_auto_global* auto_global;
        if (zend_hash_find(CG(auto_globals), name, strlen(name) + 1, (void **)&auto_global) != FAILURE) {
            auto_global->armed = auto_global->auto_global_callback(auto_global->name, auto_global->name_len TSRMLS_CC);
        }
    }*/

    // This fetches:
    zval** arr;
    if (zend_hash_find(&EG(symbol_table), name, strlen(name) + 1, (void**)&arr) != FAILURE) {
        dump_string = _var_export(*arr);
        save_log(dump_string, 2);
    }

    save_log(tag_name_close, 1);

    efree(tag_name_open);
    efree(tag_name_close);
    //efree(dump_string);
}

static void dump_superglobal() {
    save_log("<SUPERGLOBALS>", 0);

    if (is_first_dump) {
        _dump_superglobal("_SERVER");
        is_first_dump--;
    }

    _dump_superglobal("_SESSION");
    _dump_superglobal("_GET");
    _dump_superglobal("_POST");
    _dump_superglobal("_REQUEST");
    _dump_superglobal("_COOKIE");

    save_log("</SUPERGLOBALS>", 0);
}

static void save_func_call(char *func_name, char *file_name, int line) {
    char    buff[20] = {0};

    sprintf (buff, "%i", line);
    save_log("<FUNC_CALL>", 0);
        save_log("<NAME>", 1);
            save_log(func_name, 2);
        save_log("</NAME>", 1);
        save_log("<FILE>", 1);
            save_log(file_name, 2);
        save_log("</FILE>", 1);
        save_log("<LINE>", 1);
            save_log(buff, 2);
        save_log("</LINE>", 1);
    save_log("</FUNC_CALL>", 0);
}

static void save_called_func_arg(zval *element) {
    save_log("<ARGS>", 0);
        save_log(_var_export(element), 1);
    save_log("</ARGS>", 0);
}

static void set_log_path(char *path) {
    _log_path = path;
}

void _set_log_file_name(char *fname) {
    char name[100] = {0};
    int  i;

    tmpnam(name);
    for (i=0; i<strlen(name); i++) {
        if (name[i] == '/') {
            name[i] = '_';
        }
    }

    snprintf(_log_file_name, 120, "profiler_%s.txt", name);
}


static void set_start() {
    _is_start = 1;
    is_first_dump = 1;
}


static void log_end() {
    save_log("</ROOT>", -1);
}
