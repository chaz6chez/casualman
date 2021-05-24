**<center>This frame has dedicated my dear pet , Bunny Nuomi</center>**

**<center><谨以此框架献给我亲爱的宠物，兔子糯米></center>**

# Casual Man <草率之人>

***
![](https://img.shields.io/github/issues/Chaz6Chez/casualman)
![](https://img.shields.io/github/forks/Chaz6Chez/casualman)
![](https://img.shields.io/github/stars/Chaz6Chez/casualman)
![](https://img.shields.io/github/license/Chaz6Chez/casualman)
![](https://poser.pugx.org/casual/framework/v/stable)

## 介绍
- Low performance Service Framework Component for PHP based on Workerman.
- 基于Workerman的低性能PHP服务框架。
## 结构
~~~
|-- bin <启动器>
    |-- app.php
    |-- .env
|-- config  <配置项>
|-- runtime <临时目录>         \临时目录区仅app.log为固定位置，其余可自行规范
    |-- app.log
    |-- log
        |-- 202105
            |-- error.log
|-- src <源码区>               \源码区域可自行规范，以下示例按照进程区
    |-- Common <公共内容>
        |-- Process
    |-- Demo   <Demo进程>
        |-- Contorller
        |-- Model
        |-- Service
        |-- Middleware
    |-- Log <Log进程>
        |-- Contorller
        |-- Model
        |-- Service
        |-- Middleware
    |-- Mq <Mq进程>
|-- vendor <组件>
|-- .env.example
|-- composer.json
|-- LICENSE
|-- README.md
~~~
## 使用
- 以1.0.0版本举例
~~~
    composer create-project casual/casualman 你的项目名称 1.0.0
~~~

## 1.说明
- 常驻内存
- 容器
- 中间件
- 简易路由
- 可拓展进程
- 可拓展配置
- 基于medoo的数据库驱动

## 2.启动项（可拓展进程）
- 在src/Common/Process中添加Kernel/AbstractProcess的子类
- 在config.process进行配置
- AbstractProcess抽象类实现了HandlerInterface为基础进程
- ListerInterface为监听进程接口

## 3.容器
- 基于Psr/ContainerInterface的任何容器都可使用
- 默认容器为简单的单例容器Kernel/Container
- 在config.container中进行配置

## 4.路由
- 基础的适合内部JsonRpc的路由
- 可继承/重写拓展为适配HTTP或其他类型协议的路由
- 路由包含中间件

## 5.中间件
- 洋葱型中间件

## 6.数据库
- 默认使用基于Medoo改良的适合常驻的数据库驱动Database/Driver
- 提供基础的Model抽象类Database/AbstractModel
- 提供简单的链式调用连接器类Database/Connection
