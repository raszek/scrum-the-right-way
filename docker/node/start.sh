#!/bin/sh
set -e

npm install --quiet

chown -R 1000:1000 node_modules

npm run dev
