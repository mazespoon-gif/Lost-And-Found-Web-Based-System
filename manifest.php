<?php
header('Content-Type: application/manifest+json');
readfile(__DIR__ . '/manifest.json');
