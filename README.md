# ThinkWorker
Run without Apache/Nginx/php-fpm! A PHP Framework based on Workerman to help build your website!

This is inspired by a lot of other excellent frameworks, namely ThinkPHP, Laravel, Express.Js ... 

But what makes this different from ThinkPHP or Laravel is that: ThinkWorker runs without Apache or Nginx or PHP-fpm. Instead, it runs purely and stably on PHP socket connections, thanks to Workerman, which empowers it to preform much better.

This project is still in progress moving forward to the very first usable version. Now, everything may be a little bad implemented or seldomly documented. But, I will keep up. :)

Workerman Engine is in the directory (`thinkcore\lib\workerman`), and the uploaded one is for Windows. If you wanna run this on Linux, you can replace the content of this directory with the linux version of Workerman.

View(Template Engine) is powered by `Smarty`.
Model is powered by `Eloquent ORM` (from the famous Laravel)

## Very Quick Start
Simply Run The Command: `php start.php start`
Then open your browser, visit localhost!

## To Do
- Exception Tracing Webpage Templates
- Smarty file resource dirpath setting
- so much more