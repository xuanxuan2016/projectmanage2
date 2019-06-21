# 目录

* [表结构修改](#1)
* [ecolumns表修改](#2)
* [页面视图修改](#3)
* [视图字段翻译](#4)
* [增加页面及按钮权限](#4)

---

### <div id="1">表结构修改</div>

<p style="color:#c7254e;">Tips:表名与字段名根据需求替换</p>

**1.创建新表**

```sql
/*表存在先删除再创建(必要操作)*/
drop table if exists interview_business;
/*创建表(必要操作)*/
create table if not exists interview_business (
    id int not null AUTO_INCREMENT comment '主键',/*必要字段，根据实际情况选择(tinyint,smallint,int,bigint)*/
    create_date datetime not null default now() comment '创建时间',/*必要字段*/
    update_date datetime not null default now() comment '更新时间',/*必要字段*/
    business_type varchar(50) not null comment '业务类型',
    db_type varchar(50) not null comment '数据库类型',
    db_host varchar(100) not null comment '数据库地址',
    db_name varchar(100) not null comment '数据库名称',
    db_port varchar(100) not null comment '数据库端口',
    db_username varchar(100) not null comment '数据库用户名',
    db_password varchar(100) not null comment '数据库密码',
    status char(2) not null comment '状态',
    primary key(id)/*主键，必要操作*/
);
/*唯一索引(必要操作-如果业务上具有唯一特性的字段)*/
alter table interview_business add constraint uk_interview_business_business_type unique key (business_type);
/*普通索引(可选操作)*/
/*单字段*/
create index idx_interview_business_db_type on interview_business(db_type);
/*多字段*/
create index idx_interview_business_db_username_db_password on interview_business(db_username,db_password);
```

**2.为已有表增加字段**

```sql
/*添加字段，有默认值*/
delimiter $
drop procedure if exists aa$
create procedure aa()
begin
    if not exists (select column_name from information_schema.columns where table_schema='interview' AND table_name='interview_business' AND column_name='newcolumn')
    then
        alter table interview_business add newcolumn varchar(10) not null default '' comment '新增字段，有默认值';
    end if;
end $
call aa() $
delimiter ;
/*添加字段，无默认值*/
delimiter $
drop procedure if exists aa$
create procedure aa()
begin
    if not exists (select column_name from information_schema.columns where table_schema='interview' AND table_name='interview_business' AND column_name='newcolumn')
    then
        alter table interview_business add newcolumn varchar(10) not null comment '新增字段，无默认值';
    end if;
end $
call aa() $
delimiter ;
```

**3.为已有字段修改索引，默认值**

```sql
/*增加表的主键索引*/
alter table interview_business add constraint pk_interview_business_id primary key (id);
/*删除表的主键索引*/
alter table interview_business drop primary key;

/*增加表的唯一索引*/
alter table interview_business add constraint uk_interview_business_business_type unique key (business_type);
/*删除表的唯一索引*/

/*增加表的普通索引*/
alter table interview_business add index idx_interview_business_status(status);
/*删除表的普通索引*/
alter table interview_business drop index idx_interview_business_status;

/*增加默认值*/
alter table interview_business alter db_port set default '3306';
/*删除默认值*/
alter table interview_business alter db_port drop default;
```

---

### <div id="2">ecolumns修改</div>

<p style="color:#c7254e;">Tips:字段信息需要从表所在库中获取，然后插入到主库表中</p>

**1.整个表处理ecolumns字段**

```sql
/*1.首先通过如下脚本从表所在库获取插入数据*/
select concat('insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values(''',id,''',''',tablename,''',''',colname,''',''',cname,''',',isnull,',''',sqltype,''',',datatype,',',datalen,',''',alias,''');') from
    (select CONCAT_WS('.',TABLE_NAME,COLUMN_NAME) as id,TABLE_NAME as tablename,COLUMN_NAME as colname,case when COLUMN_COMMENT='' then COLUMN_NAME else COLUMN_COMMENT end as cname,
            case when IS_NULLABLE='NO' then 0 else 1 end as isnull,DATA_TYPE as sqltype,
            case when DATA_TYPE in ('tinyint','smallint','mediumint','int','integer','bigint','float','double','decimal') then 2
                 when DATA_TYPE in ('date','datetime','timestamp','time','year') then 3
                 when DATA_TYPE in ('mediumblob','mediumtext','longblob','longtext','blob','text','tinyblob','tinytext','char','varchar') then 1
                 else 1 end as datatype,
            case when CHARACTER_MAXIMUM_LENGTH is NULL then -1 else CHARACTER_MAXIMUM_LENGTH end as datalen,
            CONCAT_WS('_',TABLE_NAME,COLUMN_NAME) as alias
      from   information_schema.COLUMNS  
      where  TABLE_NAME in ('empbadgedetail') 
    )a
/*2.从主库删除原来数据(条件为表名)*/
delete ecolumns where tablename='tablename'; 
/*3.向主库插入第一步获取的数据(如以下语句)*/
insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values('empbadgedetail.id','empbadgedetail','id','主键',0,'int',2,-1,'empbadgedetail_id');
insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values('empbadgedetail.empid','empbadgedetail','empid','员工ID',0,'int',2,-1,'empbadgedetail_empid');
insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values('empbadgedetail.customerid','empbadgedetail','customerid','客户ID',0,'int',2,-1,'empbadgedetail_customerid');
insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values('empbadgedetail.type','empbadgedetail','type','申请类型',0,'char',1,2,'empbadgedetail_type');
```

**2.表中的某些字段处理ecolumns字段**

```sql
/*1.首先通过如下脚本从表所在库获取插入数据*/
select concat('insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values(''',id,''',''',tablename,''',''',colname,''',''',cname,''',',isnull,',''',sqltype,''',',datatype,',',datalen,',''',alias,''');') from
    (select CONCAT_WS('.',TABLE_NAME,COLUMN_NAME) as id,TABLE_NAME as tablename,COLUMN_NAME as colname,case when COLUMN_COMMENT='' then COLUMN_NAME else COLUMN_COMMENT end as cname,
            case when IS_NULLABLE='NO' then 0 else 1 end as isnull,DATA_TYPE as sqltype,
            case when DATA_TYPE in ('tinyint','smallint','mediumint','int','integer','bigint','float','double','decimal') then 2
                 when DATA_TYPE in ('date','datetime','timestamp','time','year') then 3
                 when DATA_TYPE in ('mediumblob','mediumtext','longblob','longtext','blob','text','tinyblob','tinytext','char','varchar') then 1
                 else 1 end as datatype,
            case when CHARACTER_MAXIMUM_LENGTH is NULL then -1 else CHARACTER_MAXIMUM_LENGTH end as datalen,
            CONCAT_WS('_',TABLE_NAME,COLUMN_NAME) as alias
      from   information_schema.COLUMNS  
      where  TABLE_NAME in ('tablename') and COLUMN_NAME in ('colname1','colname2','colname3')
    )a
/*2.从主库删除原来数据(条件为表名+字段名)*/
delete ecolumns where tablename='tablename' and colname in ('colname1','colname2','colname3'); 
/*3.向主库插入第一步获取的数据(如以下语句)*/
insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values('empbadgedetail.id','empbadgedetail','id','主键',0,'int',2,-1,'empbadgedetail_id');
insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values('empbadgedetail.empid','empbadgedetail','empid','员工ID',0,'int',2,-1,'empbadgedetail_empid');
insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values('empbadgedetail.customerid','empbadgedetail','customerid','客户ID',0,'int',2,-1,'empbadgedetail_customerid');
insert into ecolumns(id,tablename,colname,cname,isnull,sqltype,datatype,datalen,alias) values('empbadgedetail.type','empbadgedetail','type','申请类型',0,'char',1,2,'empbadgedetail_type');
```

---

### <div id="3">页面视图修改</div>

<p style="color:#c7254e;">Tips1:视图列的具体属性根据需求及数据库表结构中的eoprviewcols字段说明设置</p>
<p style="color:#c7254e;">Tips2:视图名称，表名，字段名根据需求替换</p>

**1.创建新的视图**

```sql
/*删除原有视图列*/
delete from eoprviewcols where vid in (select vid from eoprview where viewgroup='角色管理aa' and cname='角色管理aa');
/*删除原有视图*/
delete from eoprview where viewgroup='角色管理aa' and cname='角色管理aa';
/*添加视图*/
insert into eoprview(cname,ename,listindex,status,createdate,updatedate,customerid,viewgroup,
    lockindex,maintable,tablesource,tablecontent,sortexpression,tablewhere,hastotalrow,customeridcol,orgidcol,carttype,
    baroutput,barselect,barsearch,pagesearchholder,pagesearchwhere,viewkey,oprwhere,checkboxwhere)
values('角色管理aa','',1,'01',now(),now(),0,'角色管理aa',
    1,'role','role role','role','role.updatedate desc','role.status=''01''',0,'role.customerid','','',
    0,0,1,'角色名称','role.cname','role.id','','');
/*添加视图列*/
set @vid=(select last_insert_id());  
insert into eoprviewcols(vid,colname,headername,coltype,halign,headerwidth,colwidth,format,dictname,listindex,
    updatedate,expression,isoutput,isvisible,issort,issum)
select @vid,CONCAT_WS('.',tablename,colname),'角色名称','dialog-org.account.rolemanage.listviewrole','center',30,100,'','',1,
    now(),'',1,1,1,0
    from ecolumns where tablename='role' and colname='cname';
insert into eoprviewcols(vid,colname,headername,coltype,halign,headerwidth,colwidth,format,dictname,listindex,
    updatedate,expression,isoutput,isvisible,issort,issum)
select @vid,CONCAT_WS('.',tablename,colname),'创建时间','','center',30,100,'Y-m-d','',2,
    now(),'',1,1,1,0
    from ecolumns where tablename='role' and colname='createdate';
insert into eoprviewcols(vid,colname,headername,coltype,halign,headerwidth,colwidth,format,dictname,listindex,
    updatedate,expression,isoutput,isvisible,issort,issum)
select @vid,CONCAT_WS('.',tablename,colname),'更新时间','','center',30,100,'Y-m-d','',3,
    now(),'',1,1,1,0
    from ecolumns where tablename='role' and colname='updatedate';
insert into eoprviewcols(vid,colname,headername,coltype,halign,headerwidth,colwidth,format,dictname,listindex,
    updatedate,expression,isoutput,isvisible,issort,issum)
select @vid,CONCAT_WS('.',tablename,colname),'操作','del-org.account.rolemanage.listdelrole','center',10,100,'','',4,
    now(),'',0,1,0,0
    from ecolumns where tablename='role' and colname='id';
```

**2.为已有视图增加字段**

```sql
delimiter $
drop procedure if exists aa$
create procedure aa()
begin
    /*获取目标视图vid*/
    set @vid=(select vid from eoprview where viewgroup='角色管理aa' and cname='角色管理aa');
    /*如果字段不存在则添加*/
    if not exists(select 1 from eoprviewcols where vid=@vid and colname='role.status')
    then
        /*获取字段要插入哪个字段之后的位置*/
        set @listindex=(select listindex from eoprviewcols where vid=@vid and colname='role.createdate');
        /*将插入位置之后的字段的listindex都加上插入字段的个数*/
        update eoprviewcols set listindex=listindex+1 where vid=@vid and listindex>@listindex;
        /*插入要增加的字段，listindex为@listindex+1*/
        insert into eoprviewcols(vid,colname,headername,coltype,halign,headerwidth,colwidth,format,dictname,listindex,
            updatedate,expression,isoutput,isvisible,issort,issum)
        select @vid,CONCAT_WS('.',tablename,colname),'状态','','center',30,100,'','',@listindex+1,
            now(),'',1,1,1,0
            from ecolumns where tablename='role' and colname='status';
        /*插入其它字段，listindex为@listindex+n*/
        /*..............*/
    end if;
end $
call aa() $
delimiter ;
```

---

### <div id="4">视图字段翻译</div>

**1.字典翻译**

<p style="color:#c7254e;">Tips:id为【表别名+字段名】组成</p>

```sql
delete from translatedictdata where id='role.status';
insert into translatedictdata(id,code,value) values('role.status','01','有效');
insert into translatedictdata(id,code,value) values('role.status','06','无效');
```

**2.数据表翻译**

```sql
delete from translatelistdata where id='attachfilename';
insert into translatelistdata(id,tablename,fieldnames,type,condition,customeridcol)
    values('attachfilename','attach','attid,cname','','','');
delimiter ;
```

---

### <div id="5">增加页面及按钮权限</div>

<p style="color:#c7254e;">Tips:请从主管处获取所有步骤使用的code值与第二步使用的所有语句</p>

**第一步：删除涉及修改的权限配置及所有账号下此次修改的权限**

```sql
delete from eauthinterface where authid in (select id from einterface where code in ('050203','05020301','05020302','05020303','05020304'));
delete from einterface where code in ('050203','05020301','05020302','05020303','05020304');
```

**第二步:新增权限配置**

```sql
insert into einterface(cname,code,icode,itype,url,status,icon) values('角色管理','050203','Org.Account.RoleManage','2','/rolemanage','01','');
insert into einterface(cname,code,icode,itype,url,status,icon) values('新建角色','05020301','Org.Account.RoleManage.PageNewRole','3','','01','');
insert into einterface(cname,code,icode,itype,url,status,icon) values('删除角色','05020302','Org.Account.RoleManage.ListDelRole','3','','01','');
insert into einterface(cname,code,icode,itype,url,status,icon) values('查看角色信息','05020303','Org.Account.RoleManage.ListViewRole','3','','01','');
insert into einterface(cname,code,icode,itype,url,status,icon) values('编辑角色信息','05020304','Org.Account.RoleManage.DialogEditRole','3','','01','');
```

**第三步:更新相关账号的权限**

```sql
/*1.为所有管理员账号增加指定权限*/
insert into eauthinterface(role,authid,isauthen,createdate,updatedate)
    select a.role,b.id,1,now(),now()
      from (
        select distinct o.role 
                     from operator o,eauthinterface ei,einterface i 
                    where o.role=ei.role and ei.isauthen=1 and ei.authid=i.id and o.isadmin=1 and i.code='030201' and i.status='01' /*页面权限*/
      ) a
      join einterface b on 1=1
     where b.status='01' and b.code in ('05020304') /*按钮权限*/
/*2.为指定账号增加指定权限*/
insert into eauthinterface(role,authid,isauthen,createdate,updatedate)
    select a.role,b.id,1,now(),now()
      from (
        select distinct role
            from operator
            where status='01' and username in ('账号1','...','账号n')
      ) a
      join einterface b on 1=1
     where b.status='01' and b.code in ('050203','05020301','05020302','05020303','05020304')
/*3.为拥有按钮对应页面权限的所有账号添加按钮权限*/
insert into eauthinterface(role,authid,isauthen,createdate,updatedate)
    select a.role,b.id,1,now(),now()
      from (
        select distinct role
            from eauthinterface a
                join einterface b on a.authid=b.id
            where a.isauthen=1 and b.status='01' and b.code='060101' /*页面权限*/
      ) a
      join einterface b on 1=1
     where b.status='01' and b.code in ('05020304') /*按钮权限*/

```

---
