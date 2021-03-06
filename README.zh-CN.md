# Swoft Component

[![Actions Status](https://github.com/swoft-cloud/swoft-component/workflows/Unit-tests/badge.svg)](https://github.com/swoft-cloud/swoft-component/actions)
[![Build Status](https://travis-ci.org/swoft-cloud/swoft-component.svg?branch=master)](https://travis-ci.org/swoft-cloud/swoft-component)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/swoft-cloud/swoft-component)](https://github.com/swoft-cloud/swoft-component)

这里是swoft基础和核心组件的开发仓库，所有的核心组件都是由这里分发出去的。

## [English](README.md)

## 如何使用

添加组件到`composer.json`

```json
"require": {
    "swoft/component": "dev-master as 2.0"
}
```

安装:

```bash
composer update
```

## 测试

### 单元测试

快速运行测试:

```bash
// For all components
./phpunit.sh all
// For multi components
./phpunit.sh db event
// For one component
./phpunit.sh event
```

测试指定的组件:

```bash
./phpunit.sh event
// use run.php
php run.php -c src/event/phpunit.xml
// filter test method name
php run.php -c src/event/phpunit.xml --filter testAddModule
```

输出测试覆盖率:

```bash
// output coverage. require xdebug ext
phpunit --coverage-text
// output coverage without xdebug
phpdbg -dauto_globals_jit=Off -qrr /usr/local/bin/phpunit --coverage-text
phpdbg -dauto_globals_jit=Off -qrr run.php --coverage-text -c src/event/phpunit.xml
```

### 真实测试

真实测试需要启动测服务器。

```bash
# websocket 测试服务器, 可同时支持ws,http,rpc,tcp服务
php test/bin/swoft ws:start
```

端口（都在原有的基础上加了10000）：

- http 28308
- ws 28308
- rcp 28307
- tcp 28309

运行测试：

```bash
export RUN_SERVER_TEST=ws,http,tcp; ./phpunit.sh websocket-server
```

> `export RUN_SERVER_TEST=ws,tcp` 测试代码里通过判断这个设置来确定是否执行真实的请求测试

## 版本发布

需要使用工具 https://github.com/swoftlabs/swoft-releasecli

### 自动发布

使用 Github PR 评论发布组件新版本。

评论格式:

```text
@swoftbot release {VERSION} {REPOs}
```

> 注意：`@swoftbot` 必须从行头开始，后面的操作指令不能换行

使用示例:

```text
# 所有的组件
@swoftbot release v2.0.8 all

# 指定的组件
@swoftbot release v2.0.8 bean,event
```

### 手动发布

需要到机器上使用 releasecli 工具执行对应命令来发布操作。

## 使用文档

- [中文文档](https://www.swoft.org/docs)
- [English](https://en.swoft.org/docs)

## 参与讨论

- Forum https://github.com/swoft-cloud/forum/issues
- Gitter.im https://gitter.im/swoft-cloud/community
- Reddit https://www.reddit.com/r/swoft/
- QQ Group1: 548173319      
- QQ Group2: 778656850

## 参与贡献

开发组非常欢迎各位向我们提交PR(_Pull Request_)，但是为了保证代码质量和统一的风格，向官方的主仓库 [swoft/swoft](https://github.com/swoft-cloud/swoft) 和 开发仓库贡献代码时需要注意代码和commit格式

### 发起PR时的注意事项

- 请不要提交PR到各个组件子仓库，它们都是 **只读的**
- 核心组件的 _开发仓库_ 是 **[swoft/swoft-component][core]**
- 扩展组件的 _开发仓库_ 是 **[swoft/swoft-ext][ext]**
- 请 `fork` 对应的开发仓库，修改后，请把你的PR提交到对应的开发仓库

> 发布版本时官方会将代码同步到各个子仓库
### Commit Message

- commit message 只能是英文信息
- 请尽量保证commit message是有意义的说明
- 最好以 `add:` `update:` `fix:` 等关键字开头

### 代码风格

- 提交的PHP代码 **必须** 遵循 PSR-2 代码风格
- 合理且有意义的类、方法、变量命名
- 适当的注释，合理的使用空行保持代码的简洁，易于阅读
- 不要包含一些无意义的信息 例如 `@author` 等(_提交者是能够从commit log里看到的_)


[core]: https://github.com/swoft-cloud/swoft-component
[ext]: https://github.com/swoft-cloud/swoft-ext
