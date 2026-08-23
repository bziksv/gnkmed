# gnkmed.ru — документация проекта

Bitrix-интернет-магазин гинекологического медицинского оборудования и инструментов.

Шаблон: **medical-templates**. Кастомные компоненты **nbrains** (каталог, рекомендации). Модули: **prime.alerts**, **prime.cleaner**, **prime.roistatbitrixcms**, **prime.updateprice**, **niges**, **thebrainstech.copyiblock**.

## Репозиторий и окружения

| | |
|---|---|
| GitHub | https://github.com/bziksv/gnkmed |
| **Git root** | `gnkmed.ru/` (= корень сайта на prod) |
| Prod IP | `45.90.35.63` (SSH: `almamed`) |
| Prod path | `/var/www/gnkmed.ru/data/www/gnkmed.ru` |
| Домен | https://gnkmed.ru |
| Локально | http://127.0.0.1:8095/ |

Родительская папка `gnkmed/` — **не в git**: дамп БД (`gnkmed_ru_db.sql`), `.cursorignore`.

## Структура

```
gnkmed/                        # workspace (Mac)
├── gnkmed_ru_db.sql           # дамп БД (не в git)
├── .cursorignore
└── gnkmed.ru/                 # git root
    ├── .cursor/rules/         # правила Cursor (опционально)
    ├── .local/                # nginx/php-fpm (Mac)
    ├── docs/                  # документация
    ├── scripts/               # dev + deploy
    ├── bitrix/
    │   ├── modules/           # в git (кастомные + сторонние)
    │   ├── components/nbrains/
    │   └── templates/medical-templates/
    ├── local/modules/         # prime.alerts и др.
    ├── upload/                # не в git
    └── catalog/
```

## Git — что в репозитории

**В git:** код сайта, `bitrix/modules/`, шаблоны, компоненты, `local/`, `scripts/`, `docs/`, `.local/*.example`.

**Не в git:** `upload/`, кэш Bitrix, секреты (`.settings.php`, `dbconn.php`, `license_key.php`, `.htaccess`), дампы `*.sql`, файлы верификации поисковиков.

## База данных (prod)

| Параметр | Значение |
|----------|----------|
| Host | `localhost` |
| Database | `gnkmed_ru_db` |
| User | `gnkmed_ru_usr` |

Локально: `gnkmed_local` / `gnkmed_local`, БД `gnkmed_ru_db` (см. `.local/db.env`).

Инфоблок каталога: `IBLOCK_CATALOG = 33` (в `bitrix/php_interface/init.php`).

## Локальная разработка (Mac, soft)

Порты: **8095** (nginx), **9095** (php-fpm). MySQL 3306 (Homebrew).

```bash
cd gnkmed.ru
cp .local/db.env.example .local/db.env   # один раз
./scripts/setup-local-db.sh --background # один раз, импорт дампа
./scripts/start-dev.sh
./scripts/stop-dev.sh
```

Soft-режим: php-fpm `ondemand`, max 4 workers, **512M** RAM.

### Занятые порты (соседние проекты)

| Порт | Проект |
|------|--------|
| 8080 | almamed |
| 8081 | akvasan-shop |
| 8082 | vilmed |
| 8084 | polimer |
| 8085 | lormag |
| 8086 | metplus-vrn |
| 8087 | oftalmag |
| 8088 | metprof-vrn |
| 8089 | vrn-ehk |
| 8090 | medplakaty |
| 8091 | miinox |
| 8092 | argument-uk |
| 8093 | dckljaksa |
| 8094 | fasad36 |
| **8095** | **gnkmed** |

## Деплой на prod

**Git после правок — всегда.** **Prod — только по явной просьбе.**

1. Обычно: `git commit` + `git push origin main`.
2. Когда попросили выкатить: `./scripts/deploy-prod.sh` (pull с GitHub на prod).

```bash
cd gnkmed.ru
git add … && git commit -m "…" && git push origin main

# только когда пользователь просит:
./scripts/deploy-prod.sh
```

**Запрещено:** автодеплой на prod, правки на prod без commit, `scp` файлов кода.

## Интеграции

- **Roistat** — модуль `prime.roistatbitrixcms`
- **1С** — `hand1CtoSite.php` (ручная выгрузка)
- **Оптимизация** — artrix.imageoptimizer, arturgolubev.cssinliner, delight.webpconverter, dev2fun.imagecompress

## Проверка после изменений

```bash
curl -sS -o /dev/null -w '%{http_code}\n' https://gnkmed.ru/
```

Локально: `./scripts/start-dev.sh` и curl на `:8095`.

## Шаблон medical-templates: CSS/JS

На **prod** Bitrix часто подключает `.min.css` / `.min.js`. После правок стилей и скриптов пересобирать min-файлы, иначе на prod останется старая вёрстка.

```bash
cd gnkmed.ru
npx clean-css-cli -o bitrix/templates/medical-templates/template_styles.min.css \
  bitrix/templates/medical-templates/template_styles.css
```

После выката на prod сбросить `bitrix/cache/*` (делает `deploy-prod.sh`).
