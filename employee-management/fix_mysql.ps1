$path = 'C:\ProgramData\MySQL\MySQL Server 8.0\my.ini'
$lines = Get-Content $path
$newlines = $lines | Where-Object { $_ -notmatch 'skip-grant-tables' }
[System.IO.File]::WriteAllLines($path, $newlines)
Restart-Service MySQL80 -Force
