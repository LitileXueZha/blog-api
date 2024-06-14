# syntax=docker/dockerfile:1
FROM mysql:debian

WORKDIR /root
ENV MYSQL_ROOT_PASSWORD=123456
ENV TZ=Asia/Shanghai
EXPOSE 80 443 3306

# RUN sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-Linux-*
# RUN sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-Linux-*
RUN sed -i 's/deb.debian.org/mirrors.tuna.tsinghua.edu.cn/g' /etc/apt/sources.list

RUN apt update && apt install -y nginx php php7.4-fpm php7.4-mbstring php7.4-mysql

RUN sed -i 's/\[mysqld]/[mysqld]\ndefault_authentication_plugin=mysql_native_password\nperformance_schema=OFF/' /etc/mysql/my.cnf
RUN mkdir /run/php
RUN cp /usr/lib/php/7.4/php.ini-development /etc/php/7.4/cli/php.ini
COPY etc/nginx.conf /etc/nginx/conf.d/blog.conf
# 复制 mkcert 生成的证书到当前项目
COPY etc/local.pem /root
COPY etc/local-key.pem /root
RUN chmod +x /root
# RUN /entrypoint.sh mysqld & sleep 30 && killall mysqld

CMD nginx && php-fpm7.4 && /entrypoint.sh mysqld


### 构建与启动 ###
#
# docker build -t blog .
# docker volume create blog-data
# docker run -d \
# -v C:\...\workspace\blog:/root/blog \
# -v C:\...\workspace\blog-api:/root/blog-api \
# -v C:\...\workspace\blog-admin:/root/blog-admin \
# -v C:\...\Markdown:/root/Markdown \
# -v C:\...\workspace\ye:/root/ye \
# -v blog-data:/var/lib/mysql
# -p 127.0.0.1:80:80 -p 127.0.0.1:443:443 -p 127.0.0.1:3306:3306 blog

# 进入容器，创建数据库并导入之前的数据
