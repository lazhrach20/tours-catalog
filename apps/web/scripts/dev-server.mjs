import { createServer } from "node:http";

const port = Number(process.env.PORT ?? 3000);
const host = process.env.HOST ?? "0.0.0.0";

const server = createServer((req, res) => {
  if (req.url === "/health") {
    res.writeHead(200, { "Content-Type": "application/json" });
    res.end(
      JSON.stringify({
        status: "ok",
        service: "web",
        note: "Vike SSR scaffold pending",
      }),
    );
    return;
  }

  res.writeHead(200, { "Content-Type": "text/html; charset=utf-8" });
  res.end(`<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Tours Catalog</title></head>
<body>
  <h1>Tours Catalog</h1>
  <p>Frontend placeholder — Vue + Vike will be scaffolded in a later task.</p>
</body>
</html>`);
});

server.listen(port, host, () => {
  console.log(`[web] listening on http://${host}:${port}`);
});
