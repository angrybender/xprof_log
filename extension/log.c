#include <time.h>
#include <sys/time.h>

static char *addslashes(char *str_buff);
static void save_log(char *str_buff, int indent);
static void set_log_path(char *path);
static void dump_superglobal();
static void save_func_call(char *func_name, char *file_name, char *dest_file_name, int line, zend_execute_data *data);
static void save_called_func_arg(zval *element);
static void set_start();
static void log_end();
char *int_to_str(int number);

// ====================================================================================

static char *addslashes(char *str_buff) {
    zval *args[2];
    zend_uint param_count = 1;
    zval retval_ptr;
    char *return_buff;

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
        return_buff = Z_STRVAL(retval_ptr);
        zval_dtor(&function_name);
        return return_buff;
    }
    else {
        zval_dtor(&function_name);
        return "<ERROR_PARSE_PARAM type=\"addslashes\" />";
    }
}

char *_log_path;
char _log_file_name[150] = {0};
char *_file_path;
static int _is_start = 1;
FILE        *g_log_file;

static void save_log(char *str_buff, int indent) {
    int         i, len;

    indent++;
    if (_is_start == 1) {
        _is_start = 0;
        _set_log_file_name();

        len = strlen(_log_path) + strlen(_log_file_name) + 1;
        _file_path = (char *)emalloc(len);
        snprintf(_file_path, len, "%s%s", _log_path, _log_file_name);

        save_log("<?xml version=\"1.0\" encoding=\"utf-8\" ?>", -1);
        save_log("<ROOT>", -1);
    }

    if (g_log_file == NULL) {
        g_log_file = fopen(_file_path, "a+");
    }

    // отступы для красоты
    for (i = 0; i < indent; i++) {
        fprintf(g_log_file, "%s", "\t");
    }

    fprintf(g_log_file, "%s%s", str_buff, "\n");
}

static char *_var_export(zval *element) {

    char *result_buff;

    zval *args[2];
    zend_uint param_count = 2;
    zval retval_ptr;

    args[0] = element;

    zval function_name;
    zval export_flag;
    INIT_ZVAL(function_name);
    INIT_ZVAL(export_flag);

    ZVAL_STRING(&function_name, "print_r", 1);
    ZVAL_BOOL(&export_flag, 1);
    args[1] = &export_flag;

    if (call_user_function(
            CG(function_table), NULL, &function_name,
            &retval_ptr, param_count, args TSRMLS_CC
        ) == SUCCESS
    ) {
        result_buff = addslashes(Z_STRVAL(retval_ptr));
        zval_dtor(&function_name);
        zval_dtor(&retval_ptr);
        return result_buff;
    }
    else {
        zval_dtor(&function_name);
        return "<ERROR_PARSE_PARAM type=\"var_export\" />";
    }
}

// дампим суперглобалы только если есть изменения
static int is_first_dump = 1;
static char *__cache_super_global[10];
int is_super_global_change(char *super_global, char *value) {

    int array_index = -1;
    if (strcmp(super_global, "_GET") == 0) {
        array_index = 0;
    }

    if (strcmp(super_global, "_POST") == 0) {
        array_index = 1;
    }

    if (strcmp(super_global, "_COOKIE") == 0) {
        array_index = 2;
    }

    if (strcmp(super_global, "_SESSION") == 0) {
        array_index = 3;
    }

    if (array_index < 0) {
        return 1;
    }

    if (__cache_super_global[array_index] != NULL && strcmp(__cache_super_global[array_index], value) == 0) {
        return 0;
    }

    __cache_super_global[array_index] = (char *)emalloc(strlen(value)+1);
    snprintf(__cache_super_global[array_index], strlen(value)+1, "%s", value);

    return 1;
}

static void _dump_superglobal(char *name) {
    zend_auto_global    *auto_global;
    char                *tag_name_open;
    char                *tag_name_close;
    int                 len;
    char                *dump_string;
    int                 is_log_save = 1;

    len = strlen(name) + 4;
    tag_name_open = (char*)emalloc(len);
    snprintf(tag_name_open, len, "<%s>", name);

    tag_name_close = (char*)emalloc(len);
    snprintf(tag_name_close, len, "</%s>", name);

    save_log(tag_name_open, 2);

    // This fetches:
    zval** arr;
    if (zend_hash_find(&EG(symbol_table), name, strlen(name) + 1, (void**)&arr) != FAILURE) {
        if (zend_hash_num_elements(Z_ARRVAL_P(*arr))) {
            dump_string = _var_export(*arr);

            // check for diff GET:
            if (is_super_global_change(name, dump_string)) {
                is_log_save = 1;
            }
            else {
                is_log_save = 0;
            }

            if (is_log_save) {
                save_log(dump_string, 3);
            }
        }
    }

    save_log(tag_name_close, 2);

    efree(tag_name_open);
    efree(tag_name_close);
}

static void dump_superglobal() {
    save_log("<SUPERGLOBALS>", 1);

    if (is_first_dump) {
        _dump_superglobal("_SERVER");
        is_first_dump--;
    }

    _dump_superglobal("_SESSION");
    _dump_superglobal("_GET");
    _dump_superglobal("_POST");
    //_dump_superglobal("_REQUEST");
    _dump_superglobal("_COOKIE");

    save_log("</SUPERGLOBALS>", 1);
}

static void save_func_call(char *func_name, char *file_name, char *dest_file_name, int line, zend_execute_data *data) {
    char    open_tag[100] = {0};
    char    file_open_tag[100] = {0};

    void **p_args;
    int arg_count;
	int i_args;

    struct timeval time;
    gettimeofday(&time, NULL);
    long microsec = ((unsigned long long)time.tv_sec * 1000000) + time.tv_usec;
    sprintf (open_tag, "<FUNC_CALL timestamp=\"%llu\">", microsec);
    sprintf (file_open_tag, "<FILE line=\"%i\">", line);

    save_log(open_tag, 0);

        dump_superglobal();

        save_log("<NAME>", 1);
            save_log(func_name, 2);
        save_log("</NAME>", 1);
        save_log(file_open_tag, 1);
            save_log(file_name, 2);
        save_log("</FILE>", 1);
         save_log("<FILE_S>", 1);
            if (strcmp(file_name, dest_file_name) != 0) {
                save_log(dest_file_name, 2);
            }
        save_log("</FILE_S>", 1);

    // extract arg of func
    if (data) {
        p_args = data->function_state.arguments;
        arg_count = (int)(zend_uintptr_t) *p_args;
        if (arg_count > 0) {
            for (i_args=0; i_args<arg_count; i_args++) {
                zval *arg;

                arg = *((zval **) (p_args-(arg_count-i_args)));
                save_called_func_arg(arg);
            }
        }
    }

    save_log("</FUNC_CALL>", 0);
}

static void save_called_func_arg(zval *element) {
    save_log("<ARGS>", 1);
        save_log(_var_export(element), 2);
    save_log("</ARGS>", 1);
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

    for (i=0; i<strlen(_log_file_name); i++) {
        _log_file_name[i] = '\0';
    }

    snprintf(_log_file_name, 120, "profiler_%s.txt", name);
}


static void set_start() {
    _is_start = 1;
    is_first_dump = 1;
}


static void log_end() {
    save_log("</ROOT>", -1);
    if (g_log_file) {
        fflush(g_log_file);
        fclose(g_log_file);
        g_log_file = NULL;
    }
    efree(_file_path);

    // очистка инкрементального буффера суперглобалов
    int i;
    for (i=0; i<10; i++) {
        if (__cache_super_global[i]) {
            efree(__cache_super_global[i]);
        }
    }
}


char *int_to_str(int number) {
    char *buff;
    buff = (char *)emalloc(20);
    snprintf(buff, 20, "%i", number);
    return buff;
}
