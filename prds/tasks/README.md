# Tasks PRDs

Эта папка — для **маленьких PRD** на отдельные фичи/улучшения каталога туров после основного MVP (`prds/tz_main.md`).

## Как пользоваться

### 1) Создать новый таск (рекомендуемый способ)

Открой новый чат и напиши:

```
/new-task <описание фичи/улучшения>
```

Опционально заголовок:

```
task title: <короткий заголовок>
/new-task <описание фичи/улучшения>
```

Ассистент создаст файл в `prds/tasks/` на основе `prds/tasks/_template.md` и вернёт путь.

### 2) Заполнить секции PRD

Минимально заполни:
- Scope (UI / API / DB / Embeddings / Infra)
- Acceptance Criteria

### 3) Запустить реализацию через оркестратор

В новом чате:

```
/run-task prds/tasks/<YYYY-MM-DD>_<slug>.md
```

## Примеры имён

- `2026-05-17_semantic-search-threshold.md`
- `2026-05-18_admin-bulk-departure-dates.md`
- `2026-05-19_yandex-map-route-editor.md`
