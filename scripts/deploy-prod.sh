#!/bin/sh
set -e
cd "$(dirname "$0")/.."
SITE_ROOT="$(pwd)"
PROD_PATH=/var/www/gnkmed.ru/data/www/gnkmed.ru
# Bare repo OUTSIDE docroot — never keep .git under www/
DEPLOY_GIT=/var/www/gnkmed.ru/data/deploy/gnkmed.git
HOST=almamed
PROD_USER=gnkmed.ru
REMOTE=origin
BRANCH=main

echo "==> Push local commits (if any)"
git status -sb | head -5
git push "$REMOTE" "$BRANCH"

echo "==> Deploy on production via git (repo outside docroot)"
ssh "$HOST" "set -e
  PROD_PATH='$PROD_PATH'
  DEPLOY_GIT='$DEPLOY_GIT'
  BRANCH='$BRANCH'
  REMOTE='$REMOTE'
  PROD_USER='$PROD_USER'

  mkdir -p \"\$(dirname \"\$DEPLOY_GIT\")\"

  if [ ! -d \"\$DEPLOY_GIT\" ]; then
    echo 'cloning bare repo outside docroot...'
    GIT_TERMINAL_PROMPT=0 git clone --bare https://github.com/bziksv/gnkmed.git \"\$DEPLOY_GIT\"
  fi

  # Never leave .git in the web root (disk + security)
  if [ -e \"\$PROD_PATH/.git\" ]; then
    echo 'removing .git from docroot...'
    rm -rf \"\$PROD_PATH/.git\"
  fi

  if [ -f \"\$PROD_PATH/bitrix/license_key.php\" ]; then
    cp -a \"\$PROD_PATH/bitrix/license_key.php\" /tmp/gnkmed_license_key.php.bak
  fi

  cd \"\$DEPLOY_GIT\"
  GIT_TERMINAL_PROMPT=0 git fetch \"\$REMOTE\" \"\$BRANCH\"
  # Update tracked files only; leave secrets/upload/untracked alone
  GIT_TERMINAL_PROMPT=0 git --work-tree=\"\$PROD_PATH\" checkout -f FETCH_HEAD -- .
  git gc --prune=now --quiet 2>/dev/null || true

  if [ ! -f \"\$PROD_PATH/bitrix/license_key.php\" ] && [ -f /tmp/gnkmed_license_key.php.bak ]; then
    cp -a /tmp/gnkmed_license_key.php.bak \"\$PROD_PATH/bitrix/license_key.php\"
    echo 'restored bitrix/license_key.php'
  fi

  chown -R \"\${PROD_USER}:\${PROD_USER}\" \"\$PROD_PATH\" \"\$DEPLOY_GIT\"
  rm -rf \"\$PROD_PATH/bitrix/cache/\"* \"\$PROD_PATH/bitrix/managed_cache/\"* \"\$PROD_PATH/bitrix/stack_cache/\"* 2>/dev/null || true
  git --git-dir=\"\$DEPLOY_GIT\" log -1 --oneline FETCH_HEAD
  echo cache_cleared
"

echo "==> Verify"
/usr/bin/curl -sS -o /dev/null -w 'home %{http_code}\n' --max-time 20 https://gnkmed.ru/
/usr/bin/curl -sS -o /dev/null -w 'git_head %{http_code}\n' --max-time 10 https://gnkmed.ru/.git/HEAD || true
echo "==> Done"
