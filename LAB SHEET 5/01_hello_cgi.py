#!/usr/bin/env python3
"""Minimal Apache CGI response."""

print("Content-Type: text/html; charset=utf-8")
print()
print("<!doctype html><html lang='en'><meta charset='utf-8'>")
print("<title>Python CGI</title><h1>Hello, World!</h1>")
print("</html>")
