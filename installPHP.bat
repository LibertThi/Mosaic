@ECHO OFF
echo [33mCopying 'php' to 'C:\php'...[0m
xcopy php C:\php\ /Q /s
echo [33mAdding 'C:\php' to current user PATH[0m
SET Key="HKCU\Environment"
FOR /F "usebackq tokens=2*" %%A IN (`REG QUERY %Key% /v PATH`) DO Set CurrPath=%%B
echo [33mMaking a backup of current PATH 'user_path_bak.txt'[0m
ECHO %CurrPath% > user_path_bak.txt
SETX PATH "%CurrPath%";"C:\php"
ECHO [32mSuccessfully installed PHP[0m
pause