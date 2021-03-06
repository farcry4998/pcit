#!/usr/bin/env sh

export DOCKER_BUILDKIT=${PCIT_DOCKER_BUILDKIT:-1}
export DOCKER_HOST=${PCIT_DOCKER_HOST}
export DOCKER_USERNAME=${PCIT_DOCKER_USERNAME}
export DOCKER_PASSWORD=${PCIT_DOCKER_PASSWORD}

# login

set +x
echo ${DOCKER_PASSWORD} | docker login -u ${DOCKER_USERNAME} --password-stdin "${PCIT_DOCKER_REGISTRY}"

# exec command

set -x

# 若 PCIT_DOCKER_REGISTRY 存在，则镜像名加上地址
[ -n "${PCIT_DOCKER_REGISTRY}" ] && PCIT_DOCKER_IMAGE="${PCIT_DOCKER_REGISTRY}/${PCIT_DOCKER_IMAGE}"

docker ${PCIT_DOCKER_COMMAND} ${PCIT_DOCKER_OPTIONS}

if [ "$PCIT_DOCKER_COMMAND" = 'build' -a "${PCIT_DOCKER_DRY_RUN}" = 0 ];then
    docker push ${PCIT_DOCKER_IMAGE}
fi
