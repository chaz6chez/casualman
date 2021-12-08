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
- *Low performance* Service Framework Component for PHP based on Workerman.
- 基于Workerman的 “低性能” PHP服务框架。
## 结构
~~~
|-- bin <启动器>
    |-- app.php
    |-- .env
|-- config  <配置项>       # 所属casual的包通常在组件包根目录下会有对应的config文件，将其复制到此即可
    ...                       # 如casual/database的database.php
|-- runtime <临时目录>     # 临时目录区
    |-- app.log               # 启动器日志文件
    ...
|-- src <源码区>           # 源码区域可自行规范，以下示例按照进程区
    |-- Common                # 公共区域
        |-- Internal            # 公共的辅助组件
        |-- Process             # 进程
    |-- Application <APP区域> # APP区域内目录结构可根据个人喜好安排
        |-- Contorller
        |-- Model
        |-- Service
        |-- Middleware
        ...
    ...                       # 可以多个APP区域
|-- vendor <组件>
|-- .env.example
|-- composer.json
|-- LICENSE
|-- README.md
~~~
## 使用
- 使用composer快速创建项目
    - 建议使用最新版casual/casualman
~~~
    composer create-project casual/casualman {your project's name} 2.2.0
~~~

## 1.说明
- 常驻内存
- 容器
- 中间件
- 简易路由
- 可拓展进程
- 可拓展配置
- 基于medoo的数据库驱动

## 2.启动项（可拓展进程）casual/frame - AbstractProcess
- 在src/Common/Process中添加Kernel/AbstractProcess的子类
- 在config.process进行配置
- AbstractProcess抽象类实现了HandlerInterface为基础进程
- ListerInterface为监听进程接口

## 3.容器 casual/frame - Container
- 基于Psr/ContainerInterface的任何容器都可使用
- 默认容器为简单的单例容器Kernel/Container
- 在config.container中进行配置

## 4.路由 casual/frame - AbstractRouter
- 基础的适合内部JsonRpc的路由
- 可继承/重写拓展为适配HTTP或其他类型协议的路由
- 路由包含中间件

## 5.中间件 casual/frame - Middlewares
- 洋葱型中间件

## 6.数据库 casual/database
- 默认使用基于Medoo改良的适合常驻的数据库驱动Database/Driver
- 提供基础的Model抽象类Database/AbstractModel
- 提供简单的链式调用连接器类Database/Connection

## 7.公共方法组件
#### src/Common/Internal 下的公共组件：
- Timer 基于workerman-timer的封装
- RateLimit 基于Redis的令牌桶限流组件
- Redlock
- Redis
- RpcClient JsonRpc2的客户端
    
#### src/Common/Process 下的公共服务进程
- HttpServer Http服务
- RpcServer 基于TCP的JsonRpc2.0服务

#### casual/until 提供的服务
- JsonRpc2协议
- Cache 上下文缓存
    - 进程间相互隔离
    - 在当前进程内常驻
    
- Context 基于Cache封装的上下文缓存
- Struct Chaz6chez/Structure组件
    - 可用于数据映射
    - 出入参过滤
    - 参数验证判断
