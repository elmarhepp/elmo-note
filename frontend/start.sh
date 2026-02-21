#!/bin/bash
set -e

npx serve -s dist -l ${PORT:-3000}
