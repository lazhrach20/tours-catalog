# Command: Create a new Task PRD from text

## When to use

Use this when you have a **free-form feature/improvement description** and want a new file in `prds/tasks/` created from the template.

---

## How to start (in chat)

Open a new chat and write:

```
/new-task <your text>
```

Optional title:

```
task title: <short title>
/new-task <your text>
```

---

## Output

The assistant will:
- create a new markdown file under `prds/tasks/` using `prds/tasks/_template.md`
- print the created file path

Then run it via:

```
/run-task <created path>
```

