# ThinkWorker

> Note that this framework is no longer maintained. You can read its source codes for learning purposes or all the way you like of course, but using it in production is strongly discouraged.

Run without Apache/Nginx/php-fpm! A PHP Framework based on `Workerman` to help build your website! mailto: derzart@gmail.com QQ:529189858

- Memory Resident and Multi-process: Load code once, and run stably and blazing fast on PHP socket connections with multiple processes. Based on `Workerman`.
- Lazy autoload: Almose every class is autoloaded, and for sure, only once, then it stays in memory.
- VHost Support: Multiple domain resolution supported. And seperate routing rules can be set for each domain.
- Flexible Routing Rules: Just like Laravel, ThinkPHP and others.
- Db & Model: Using `Eloquent ORM`, from the famous `Laravel`.
- View: Using `Smarty` engine as template engine, fast & simple.
- Static Resource Server: everything in `public` directory is accessible.
- `composer` is supported. `psr4`
- Built-in `Task Queue`! File driver and Database driver are implemented so far. And they are both multi-process safe! Database driver supports distributed deployment.
- Debug & Tracing: turn on debug mode, and you will have details and tracings for exceptions, request information and stuff...

If you have any questions, don't hesitate sumbit an issue on Github :) I'll reply soon.

## Attention
Workerman Engine is in the directory (`thinkworker\lib\workerman`), and the uploaded one is for Windows. If you want to run this on Linux, you can replace the content of this directory with the linux version of Workerman.

## Very Quick Start
Simply Run The Command: `php start.php start`
Then open your browser, visit localhost!

## Now Progress
version: `1.0.0 alpha`
