# Command: Create a new Bug report from text

## When to use

Use this when you have a **free-form bug description** and want a new file in `prds/bugs/` created from the template.

---

## How to start (in chat)

Open a new chat and write:

```
/new-bug <your text>
```

Optional title:

```
bug title: <short title>
/new-bug <your text>
```

---

## Output

The assistant will:
- create a new markdown file under `prds/bugs/` using `prds/bugs/_template.md`
- print the created file path

Then run it via:

```
/run-bug <created path>
```

