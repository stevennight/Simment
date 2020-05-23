### Simment v0.0

前端使用Vue编写（单页应用），后端使用PHP(7)+Mongo数据库驱动。

一个可以嵌入静态站点的简易评论系统，目前仍然处于开发、测试。

系统拥有基础的评论功能，以及先审核后展示、回复邮件通知、验证码（目前写死为中文验证码）、管理员回复标识、前台管理员回复等功能。

#### 安装

- PHP 7以及MongoDB环境。

- PHP安装mongodb扩展。

- 进入Backend目录通过composer安装lib。

- 根据`config.php.example`，配置好Backend下的`config.php`。

- 通过执行`php install.php`创建好数据库（集合）和索引。

- 将网站目录指向Backend/public。

- 进入Frontend执行`npm build`生成前端文件。

- 将生成的前端文件移动到Backend/public下。

- 使用`config.php`中的账号、密码（明文）登录后台管理，并在站点管理中增加您的站点（域名，非80和443则需要带上端口号）。后台地址：`http(s)://<您的域名>/#/admin`。

- 在站点页面适当位置增加以下代码（`<xxx>`代表需要您按照实际情况进行填写）：

  ```html
  <!--如果您不希望使用jsdelivr，可以自行更换jquery引入地址-->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
  <script>
    //必须将JQ赋值给$jquery变量，simment.js脚本内通过$jquery使用JQ。
    var $jquery=$.noConflict(); //将$符号还给原来的库，使用$jquery变量来使用jq，避免冲突。
  </script>
  <!--若您的网页未引入vue，请务必通过以下语句引入-->
  <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js"></script>
  <div id="nyatoriCommentWrapper0511" data-system="<评论系统地址，协议(http://或https://)+域名，末尾无'/'>" data-site="<要引入评论系统站点的域名>" data-path="<目前请留空>" data-with-style="<是否使用评论系统自带的style，使用请填写true，否则留空并且需要在自己的站点中给评论区块设置样式>" data-scroll-el="<滚动条所属元素，可以是标签名，#id或者.class等（JQ选择器）>"></div>
  <script src="<评论系统地址，同data-system>/js/simment.js"></script>
  ```

#### 注意

- 建议嵌入站点使用https，以满足目前chrome所要求的第三方cookies存储要求。（http下目前仍能正常工作）
- 评论系统可能在部分浏览器上无法正常使用，请通过Issue告知浏览器的名称，版本，以及使用的Simment版本。（目前已知移动端小米浏览器9和10都出现samesite=none的session cookie无法保存的问题，如果有好的解决办法希望可以通过Issue告知或者直接pull request，感谢）
- 如果可以合理设置cookies domain（config.php），则可以关闭cookieCors（config.php），并且可以避免以上两点问题。如评论系统为comment.nyatori.com，站点为nyatori.com，则cookies domain可以设置为`.nyatori.com`。
- 服务器中请关闭或者配置好php的open_basedir设置，否则可能导致php无法访问的问题。（如宝塔面板可以关闭open_basedir）