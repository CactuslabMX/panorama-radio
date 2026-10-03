const { execFileSync } = require('node:child_process');
const { existsSync } = require('node:fs');
const path = require('node:path');
const php = process.env.PHP_BINARY || (process.platform === 'win32' && existsSync('C:/xampp/php/php.exe') ? 'C:/xampp/php/php.exe' : 'php');
execFileSync(php, [path.join(__dirname, 'build-pages.php')], { stdio: 'inherit' });
