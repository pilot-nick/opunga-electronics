const { spawn } = require("node:child_process");
const fs = require("node:fs");
const path = require("node:path");

const PORT = Number(process.env.PORT) || 8000;
const HOST = process.env.HOST || "localhost";
const ROOT = __dirname;

function findPhp() {
  if (process.env.PHP_BIN && fs.existsSync(process.env.PHP_BIN)) {
    return process.env.PHP_BIN;
  }

  const exe = process.platform === "win32" ? "php.exe" : "php";
  const candidates = [
    "C:\\xampp\\php\\php.exe",
    "C:\\laragon\\bin\\php",
    "C:\\wamp64\\bin\\php",
    "C:\\php\\php.exe",
    "/usr/bin/php",
    "/usr/local/bin/php",
  ];

  for (const base of candidates) {
    if (base.endsWith("php") && fs.existsSync(base)) {
      return path.join(base, exe);
    }
    if (fs.existsSync(base)) {
      if (fs.statSync(base).isDirectory()) {
        const direct = path.join(base, exe);
        if (fs.existsSync(direct)) return direct;
        const versions = fs
          .readdirSync(base)
          .filter((entry) => /^php[\d.]+$/i.test(entry))
          .sort()
          .reverse();
        for (const version of versions) {
          const nested = path.join(base, version, exe);
          if (fs.existsSync(nested)) return nested;
        }
      } else {
        return base;
      }
    }
  }

  return "php";
}

const php = findPhp();
const child = spawn(php, ["-S", `${HOST}:${PORT}`, "-t", ROOT], { stdio: "inherit" });

console.log(`\n  Opunga Cyber and Electronics\n  PHP  ${php}\n  URL  http://${HOST}:${PORT}\n`);

child.on("error", (error) => {
  if (error.code === "ENOENT") {
    console.error(`Could not run "${php}". Set PHP_BIN to your php executable.`);
  } else {
    console.error(error);
  }
  process.exit(1);
});

["SIGINT", "SIGTERM"].forEach((signal) => {
  process.on(signal, () => {
    child.kill();
    process.exit(0);
  });
});
