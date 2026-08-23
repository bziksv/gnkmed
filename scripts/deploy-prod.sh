#!/bin/sh
set -e
cd "$(dirname "$0")/.."
SITE_ROOT="$(pwd)"
PROD_PATH=/var/www/gnkmed.ru/data/www/gnkmed.ru
HOST=almamed
PROD_USER=gnkmed.ru
REMOTE=origin
BRANCH=main

echo "==> Push local commits (if any)"
git status -sb | head -5
git push "$REMOTE" "$BRANCH"

echo "==> Deploy on production via git"
ssh "$HOST" "cd $PROD_PATH &&
  set -e
  if [ -f bitrix/license_key.php ]; then
    cp -a bitrix/license_key.php /tmp/gnkmed_license_key.php.bak
  fi
  if [ ! -d .git ]; then
    git init
    git checkout -B $BRANCH 2>/dev/null || git checkout -b $BRANCH
    git remote add $REMOTE https://github.com/bziksv/gnkmed.git
  else
    git remote set-url $REMOTE https://github.com/bziksv/gnkmed.git 2>/dev/null || true
  fi
  GIT_TERMINAL_PROMPT=0 git fetch $REMOTE $BRANCH
  GIT_TERMINAL_PROMPT=0 git checkout FETCH_HEAD -- .
  if [ ! -f bitrix/license_key.php ] && [ -f /tmp/gnkmed_license_key.php.bak ]; then
    cp -a /tmp/gnkmed_license_key.php.bak bitrix/license_key.php
    echo 'restored bitrix/license_key.php'
  fi
  chown -R ${PROD_USER}:${PROD_USER} .
  rm -rf bitrix/cache/* bitrix/managed_cache/* bitrix/stack_cache/* 2>/dev/null || true
  git log -1 --oneline FETCH_HEAD
  echo cache_cleared
"

echo "==> Verify"
/usr/bin/curl -sS -o /dev/null -w 'home %{http_code}\n' --max-time 20 https://gnkmed.ru/
echo "==> Done"
