# ThinkWorker
Run without Apache/Nginx/php-fpm! A PHP Framework based on `Workerman` to help build your website! mailto: derzart@gmail.com QQ:529189858

[中文文档](http://docs.thinkworker.cn/#/zh-cn/guide/essentials/)

- Memory Resident and Multi-process: Load code once, and run stably and blazing fast on PHP socket connections with multiple processes. Based on `Workerman`.
- Lazy autoload: Almose every class is autoloaded, and for sure, only once, then it stays in memory.
- VHost Support: Multiple domain resolution supported and seperate routing rules can be set for each domain.
- Flexible Routing Rules: Just like Laravel, ThinkPHP and others.
- Db & Model: Using `Eloquent ORM`, from the famous `Laravel`.
- View: Using `Smarty` engine as template engine, fast & simple.
- Static Resource Server: everything in `public` directory is accessible.
- of course `composer` is supported. `psr4`
- yes, WE HAVE `Task Queue` BUILT INSIDE! File driver or Database driver is implemented so far. And they are all multi-process safe! Database driver supports distributed deployment.
- Debug & Tracing: turn on debug mode, and you will have details and tracings for exceptions, request information and stuff...

Now ThinkWorker is still in the progress moving forward to a very first stable version. Please try out, play around and maybe test out some bugs and report them :) 

If you have any questions, do sumbit an issue on Github :) I'll reply soon.

## Attention
Workerman Engine is in the directory (`thinkworker\lib\workerman`), and the uploaded one is for Windows. If you wanna run this on Linux, you can replace the content of this directory with the linux version of Workerman.

## Very Quick Start
Simply Run The Command: `php start.php start`
Then open your browser, visit localhost!

## Now Progress
version: `1.0.0 alpha`
