$versionString=git describe --abbrev=0 --tags
(Get-Content .\src\php\pages\about.php) -Replace '<span id="pdrVersionSpan">.*</span></p>', ('<span id="pdrVersionSpan">' + $versionString + '</span></p>') | Set-Content .\src\php\pages\about.php