<img src="https://raw.github.com/uicestone/syssh/master/_doc/introduction/schedule.png" alt="日程界面" />
<img src="https://raw.github.com/uicestone/syssh/master/_doc/introduction/project.png" alt="项目列表界面" />
<img src="https://raw.github.com/uicestone/syssh/master/_doc/introduction/achievement.png" alt="报表界面" />

概要
---
这是一个基于 Web 的企业内部管理系统平台。以最简洁的方式实现了企业任务日程、客户关系、项目协作、知识共享、消息通知等功能。

架构
---
后端基于Linux, Apache, MySQL, php环境，采用Codeigniter框架编写，利用HMVC实现概念逐级抽象，单入口结构。  
前端基于css布局，单页面ajax结构，采用jQuery, jQuery UI, Backbone.js等库编写。 

理念
---
### 集合知识
分工极度细化的年代，应当减少知识的重复获取。将公司内部的知识以科学的形式索引起来，在使用的时候自动或手动匹配获取，可以让所有生产力集中在业务和获取新知识上。

### 提高效率
工作流、任务列表、日程日历、事务协作等功能将企业中的单人、多人事务管理进行规范的电子化归档，相关人员可以跟踪整个流程及涉及的相关信息。  
事务协作系统以非实时性为主，这样每个人可以专注于自己正在进行的工作。与他人进行配合时也不用担心打断他人的工作。

### 简化配置和使用
传统ERP系统需要采购昂贵的系统，经过复杂的配置方可使用。为此不得不由专业的ERP实施人员帮助配置，为次需要大量的时间和费用。  
Syssh将这一传统配置的过程拆分为简易配置和定制开发两块。  
 * 基础的企业共性功能已经被抽象出来，只要将企业的特定事务类型、人员类型、工作流等输入系统即可立即获得一套完整的管理系统。  
 * 复杂的定制功能直接基于系统的HMVC架构继承开发，并注重其他同类公司的复用。

### 企业间交互
集中部署的企业信息有助于在得到企业授权的情况下为企业提供互相之间的交易机会等信息。

部署
---

## 环境
php >= 5.4
MySQL >= 5.0

## 数据库准备
- 创建名为`syssh`的数据库
- 运行`_doc/database-structure.sql`

## 启动服务

### 使用Apache或NGINX等Web服务

- 根目录绑定为`web/`
- 所有非实际文件请求rewrite到index.php

### 使用php内置Web服务

- `cd web/; php -S localhost:8080`