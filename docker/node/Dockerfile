FROM node:22-alpine

WORKDIR /app

ADD start.sh /start.sh
RUN chmod +x /start.sh

ENTRYPOINT ["sh", "/start.sh"]
