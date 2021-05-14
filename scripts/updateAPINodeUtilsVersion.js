const packageNodeUtils = require("../sourcecode/npm/nodeUtils/package.json");
const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

const version = packageNodeUtils.version;

const folder = path.resolve(__dirname, "../sourcecode/apis");

fs.readdir(folder, (err, files) => {
  files.forEach((projectName) => {
    const projectFolder = path.resolve(folder, projectName);
    const packagePath = path.resolve(projectFolder, "package.json");
    const re = new RegExp(
      /"@cerpus\/edlib-node-utils": "\^?([0-9*]\.[0-9*]\.[0-9*])"/
    );
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
