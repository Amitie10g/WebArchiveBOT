HOME_BASENAME=$(basename "$HOME")
TOOL_NAME="$HOME_BASENAME-backend"
CONF_FILE=$HOME/bin/webarchivebot.ini
DEPLOYMENT=$HOME/bin/deployment.yaml
SELF="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MAIN=$DIR/main.php
DOCKER_IMAGE="docker-registry.tools.wmflabs.org/toollabs-php72-web:latest"
PHP_PATH="/usr/bin/php"

if [ "$1" == "boot" ]
        then
                php -c $CONF_FILE -f $MAIN
else
        cd $DIR

        if [ -f $CONF_FILE ]; then
                rm $CONF_FILE
        fi

        if [ -f $DEPLOYMENT ]; then
                rm $DEPLOYMENT
        fi

        /bin/cat << EOF > $CONF_FILE
; Do not edit this file directly! Edit WebArchiveBOT.sh instead.
date_default_timezone_set = UTC
error_reporting = "E_ALL ^ E_NOTICE"
error_log=$HOME/log/webarchivebot-error.log
EOF

        /bin/cat << EOF > $DEPLOYMENT
kind: Deployment
apiVersion: extensions/v1beta1
metadata:
  name: $TOOL_NAME
  namespace: $HOME_BASENAME
spec:
  replicas: 1
  template:
    metadata:
      labels:
        name: $TOOL_NAME
    spec:
      containers:
        - name: $TOOL_NAME
          image: $DOCKER_IMAGE
          command: [ "$SELF", "boot" ]
          workingDir: $HOME
          env:
            - name: HOME
              value: $HOME
          imagePullPolicy: Always
          volumeMounts:
            - name: home
              mountPath: $HOME
      volumes:
        - name: home
          hostPath:
            path: $HOME
EOF

        kubectl create -f $DEPLOYMENT

fi
