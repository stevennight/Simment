### Simment

前端使用Vue编写（单页应用），后端使用PHP(7)+Mongo数据库驱动。

安装：

PHP 7以及MongoDB环境。
PHP安装mongodb扩展。
进入Backend目录通过composer安装lib。
配置好Backend下的config.php。
通过执行`php install.php`创建好数据库（集合）和索引。
将网站目录指向Backend/public。
进入Frontend执行`npm build`生成前端文件。
将生成内容移动到Backend/public下。