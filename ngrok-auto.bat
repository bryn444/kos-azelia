@echo off
title Ngrok Auto-Reconnect - Kos Azelia

:loop
echo [%time%] Menjalankan tunnel ngrok ke port 8000...
ngrok http 8000 --url https://supervise-battered-coauthor.ngrok-free.dev
echo [%time%] Koneksi ngrok terputus atau berhenti. Menyambungkan ulang dalam 5 detik...
timeout /t 5 /nobreak >nul
goto loop