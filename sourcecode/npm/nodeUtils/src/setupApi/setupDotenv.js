import path from 'path';
import fs from 'fs';
import dotenv from 'dotenv';
import dotenvExpand from 'dotenv-expand';

const appPath = path.resolve(fs.realpathSync(process.cwd()));

const dotenvFiles = [`.env`, `.env.defaults`].filter(Boolean);

dotenvFiles.forEach((dotenvFile) => {
    if (fs.existsSync(dotenvFile)) {
        dotenvExpand(
            dotenv.config({
                path: `${appPath}/${dotenvFile}`,
            })
        );
    }
});
