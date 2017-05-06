#!/usr/bin/env sh
set -e

# Uncomment if you want to use HHVM
#USE_HHVM=true

/bin/cat << EOF > ${HOME}/hhvm.ini
date.timezone = UTC
hhvm.enable_obj_destruct_call = true
hhvm.enable_zend_compat = true
hhvm.error_handling.call_user_handler_on_fatals = true
hhvm.hack.lang.iconv_ignore_correct = true
hhvm.jit = true
hhvm.log.always_log_unhandled_exceptions = true
hhvm.log.native_stack_trace = false
hhvm.log.runtime_error_reporting_level = HPHP_ALL
hhvm.log.use_syslog = false
hhvm.pcre_cache_type = lru
hhvm.pid_file =
hhvm.repo.central.path = /tmp/hhvm.hhbc
hhvm.log.file=${HOME}/hhvm-webservice.log
error_log=${HOME}/hhvm-webservice-error.log
hhvm.hack.lang.look_for_typechecker = false
memory_limit = 1024M
hhvm.log.use_log_file = true
hhvm.log.level = Warning
;hhvm.repo.authoritative = true

EOF

exec /usr/bin/hhvm -m server -c
