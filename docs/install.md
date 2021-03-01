# 安装

## 环境要求

**PHP 版本** ≥ 5.6

**Windows 版本**：

- 不需要扩展依赖，但是不支持多进程，_建议用于开发调试环境_

**Linux 版本**：

- 要求 PHP 的 `pcntl`、`posix` 扩展（必须）
- 更好的并发性能：`event`、`libevent`（可选）
- HTTPS 支持：`openssl`（可选）

ThinkWorker 基于`Workerman`，环境要求与其基本一致，您可以参考其[要求](http://doc.workerman.net/315115)

## 下载源码包

您可以直接下载源码包。上述环境要求符合的情况下，您可以直接基于该源码包进行开发。

> 目前 ThinkWorker 属于测试版本，不适用于正式项目。您可以通过参与开源社区让 ThinkWorker 变得更好！

版本: 1.0.0 alpha

GitHub 仓库所含 `Workerman` 为 Windows 版本，在 Linux 请替换目录 `thinkworker/lib/workerman` 内容为对应平台版本的 `Workerman` 才能运行。

## Hello, ThinkWorker!

1. 将源码包内容解压到您项目开发目录

2. 启动 ThinkWorker

   - Windows：可以直接双击运行`start_for_win.bat`

   - Windows 或 Linux：可在 CLI 下`cd`到项目的开发目录，然后运行命令：

   ```bash
   php start.php start
   ```

3. 在本地浏览器访问 http://localhost
