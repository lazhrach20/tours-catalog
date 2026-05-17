# Bugs

Эта папка — для описаний багов каталога туров, которые нужно воспроизвести и исправить.

## Как пользоваться

### 1) Создать новый баг (рекомендуемый способ)

Открой новый чат и напиши:

```
/new-bug <описание бага>
```

Опционально заголовок:

```
bug title: <короткий заголовок>
/new-bug <описание бага>
```

Ассистент создаст файл в `prds/bugs/` на основе `prds/bugs/_template.md` и вернёт путь.

### 2) Заполнить детали

Открой созданный файл и дополни:
- Steps to reproduce
- Expected / Actual
- Evidence (логи Laravel, payload `/api/v1`, скриншоты)

### 3) Запустить фикс через оркестратор

В новом чате:

```
/run-bug prds/bugs/<YYYY-MM-DD>_<slug>.md
```

## Примеры имён

- `2026-05-17_semantic-search-500-no-fallback.md`
- `2026-05-18_tour-detail-map-empty-polyline.md`
- `2026-05-19_admin-publish-missing-validation-toast.md`
