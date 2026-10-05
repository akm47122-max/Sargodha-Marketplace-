import express from 'express';
import { createProxyMiddleware } from 'http-proxy-middleware';
import { spawn, execSync } from 'child_process';
import path from 'path';
import fs from 'fs';

const app = express();
const PORT = process.env.PORT || 3000;
const PHP_PORT = 8080;
const PHP_HOST = '127.0.0.1';

// 1. Ensure MariaDB is running
function ensureMariaDB() {
    try {
        execSync('(mariadb -u marketplace -pMarketplace123! -e "SELECT 1" || mysql -u marketplace -pMarketplace123! -e "SELECT 1") > /dev/null 2>&1');
        console.log('[Database] MariaDB is already running and accessible.');
    } catch (e) {
        console.log('[Database] Starting MariaDB server...');
        try {
            execSync('mkdir -p /run/mysqld && chown -R mysql:mysql /run/mysqld');
            spawn('/usr/sbin/mariadbd', ['--user=mysql'], {
                detached: true,
                stdio: 'ignore'
            }).unref();
            // Wait briefly for socket
            execSync('sleep 2');
            console.log('[Database] MariaDB service started successfully.');
        } catch (startErr) {
            console.error('[Database] Notice when starting MariaDB:', startErr);
        }
    }
}

// 2. Ensure PHP Built-in Server is running on port 8080
let phpProcess: any = null;
function startPhpServer() {
    try {
        execSync(`fuser -k ${PHP_PORT}/tcp > /dev/null 2>&1 || true`);
    } catch (e) {}

    console.log(`[PHP] Starting PHP 8.2 server on http://${PHP_HOST}:${PHP_PORT}...`);
    phpProcess = spawn('php', ['-S', `${PHP_HOST}:${PHP_PORT}`, '-t', process.cwd()], {
        stdio: 'inherit'
    });

    phpProcess.on('error', (err: any) => {
        console.error('[PHP] Failed to start PHP server:', err);
    });

    phpProcess.on('exit', (code: number) => {
        console.log(`[PHP] Server process exited with code ${code}. Restarting...`);
        setTimeout(startPhpServer, 1000);
    });
}

ensureMariaDB();
startPhpServer();

// 3. Serve uploads & static assets directly if needed
app.use('/uploads', express.static(path.join(process.cwd(), 'uploads')));
app.use('/assets', express.static(path.join(process.cwd(), 'assets')));

// 4. Reverse Proxy all other web requests directly to the real PHP 8.2 backend
app.use('/', createProxyMiddleware({
    target: `http://${PHP_HOST}:${PHP_PORT}`,
    changeOrigin: true,
    ws: true,
    cookieDomainRewrite: ''
}));

app.listen(PORT, () => {
    console.log(`====================================================`);
    console.log(`🚀 SargodhaMart Local Marketplace is live!`);
    console.log(`🌐 Server listening on http://localhost:${PORT}`);
    console.log(`🐘 Powered by real PHP 8.2 & MariaDB / MySQL`);
    console.log(`📍 Cities: Sargodha | Shaheenabad | Sillanwali`);
    console.log(`====================================================`);
});
