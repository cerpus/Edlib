const packageNodeUtils = require("../sourcecode/npm/nodeUtils/package.json");
const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

const args = process.argv.slice(2);

let version = packageNodeUtils.version;

const folders = [
  path.resolve(__dirname, "../sourcecode/apis"),
  path.resolve(__dirname, "../sourcecode/proxies"),
];

if (args.length !== 0) {
  version = args[0];
}

folders.forEach((folder) => {
  fs.readdir(folder, (err, files) => {
    files.forEach((projectName) => {
      const projectFolder = path.resolve(folder, projectName);
      const packagePath = path.resolve(projectFolder, "package.json");
      const re = new RegExp(/"@cerpus\/edlib-node-utils": "\^?(.*)"/);
      fs.writeFileSync(
        packagePath,
        fs
          .readFileSync(packagePath)
          .toString()
          .replace(re, `"@cerpus\/edlib-node-utils": "${version}"`)
      );

      execSync("yarn", { cwd: projectFolder, stdio: "inherit" });
    });
  });
});
