@ECHO OFF
SET BIN_TARGET=%~dp0/../vendor/robmorgan/phinx/bin/phinx
php "%BIN_TARGET%" %*
