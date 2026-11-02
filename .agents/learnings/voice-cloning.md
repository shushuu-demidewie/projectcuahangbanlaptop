# Voice Cloning

> Tổng hợp kiến thức về chức năng Huấn luyện giọng nói (Voice Cloning) dùng Piper TTS & Whisper trong dự án.
> Cập nhật lần cuối: 2026-08-27

---

## Architecture

### Kiến trúc Hệ thống Pipeline
- **Ngày**: 2026-08-27
- **Chi tiết**: Pipeline huấn luyện mô hình giọng nói bao gồm 2 giai đoạn chính: 
  1. **Data Prep**: Dùng mô hình `Whisper` để chuyển đổi âm thanh (speech-to-text), kết hợp với `pydub` để cắt âm thanh tự động ở các đoạn im lặng (silences), sau đó định dạng lại chuẩn `metadata.csv`.
  2. **Training**: Sử dụng mã nguồn `piper1-gpl` với `piper-tts[train]` để huấn luyện mô hình dựa trên format LJSpeech.
- **Thiết kế Backend**: Sử dụng `FastAPI` làm server, các tiến trình nặng (Whisper/Piper) được đẩy vào background (subprocess), và trạng thái logs được truyền real-time qua `WebSocket` về màn hình React UI.
- **Files liên quan**: `backend/app/api.py`

---

## Bugs & Solutions

### Lỗi `Fatal error in launcher` khi dùng pip trong Venv
- **Ngày**: 2026-08-27
- **Vấn đề**: Gõ lệnh `pip install ...` bị lỗi báo sai đường dẫn thư mục gốc `D:\Downloads\...` dù thư mục đã được copy sang Desktop.
- **Root cause**: Khi copy hoặc di chuyển thư mục `venv` trên Windows, file thực thi `pip.exe` bị lỗi do đường dẫn cũ bị lưu cứng (hardcoded).
- **Fix**: Chạy `python -m pip install ...` thay vì gõ `pip`. Lệnh này dùng trực tiếp môi trường python hiện tại để gọi module pip, bỏ qua file launcher `.exe` bị lỗi.

### Lỗi biên dịch C++ (`scikit-build could not get a working generator`)
- **Ngày**: 2026-08-27
- **Vấn đề**: Cài đặt `piper-tts[train]` trên Windows báo lỗi đỏ lòm thiếu C++ Compiler và CMake.
- **Root cause**: Thuật toán `monotonic_align` của Piper/VITS được viết bằng C/Cython để tối ưu tốc độ, do đó yêu cầu phải được biên dịch trên máy bằng Microsoft C++ Build Tools.
- **Fix**: Bắt buộc cài đặt **Visual Studio Build Tools** và chọn gói **Desktop development with C++**. Nếu đóng gói phần mềm bán cho khách hàng, cần tạo file Portable Python (gom cả thư mục venv đã biên dịch sẵn thư viện) để khách không cần cài C++.

---

## How-To

### Cách tạo Pipeline Auto-Dataset cho Voice Cloning
- **Ngày**: 2026-08-27
- **Bước thực hiện**:
  1. Nhận danh sách các file Audio/Video upload.
  2. Dùng `pydub.silence.split_on_silence` để cắt âm thanh ở các đoạn ngừng nói/im lặng (giúp tạo các câu nói ngắn gọn từ 3-10 giây).
  3. Dùng `whisper` model (`cpu` hoặc `cuda`) quét text của từng đoạn audio.
  4. Ghi text và tên file wav vào file `metadata.csv` (cấu trúc LJSpeech: `tên_file|text|text`).
  5. Đẩy vào script training của Piper TTS.

### Cách đóng gói (Build) phần mềm AI để phân phối trên Windows
- **Ngày**: 2026-08-27
- **Bước thực hiện**:
  1. Developer cài đầy đủ C++ Compiler và tải các thư viện AI yêu cầu Build (như `piper-tts`, `xformers`...).
  2. Chờ pip compile ra các thư viện nhị phân `.pyd` / `.dll` nằm trong `venv`.
  3. Xóa các file rác trong `venv` và nén (Zip) thư mục `venv` này lại, hoặc dùng PyInstaller pack toàn bộ.
  4. Phân phối cho khách hàng. Máy khách hàng sẽ dùng trực tiếp các module `.pyd` đã được build sẵn, không cần biết C++ là gì.

---

## Patterns

### Pattern: Giao tiếp tiến trình nặng (Subprocess) với WebSocket
- **Ngày**: 2026-08-27
- **Chi tiết**: Đối với các tác vụ dài (ví dụ: Train model AI mất hàng giờ), ta không dùng Request/Response HTTP thông thường vì sẽ bị timeout. Thay vào đó, dùng `subprocess.Popen` và đọc stream đầu ra (`stdout`), sau đó đẩy thẳng từng dòng log qua `WebSocket`.
- **Ví dụ code**:
```python
import subprocess
import asyncio

async def run_and_stream(command, websocket):
    process = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, text=True)
    
    # Chạy vòng lặp để đọc từng dòng Output của Command và gửi về Frontend
    for line in iter(process.stdout.readline, ''):
        await websocket.send_json({"type": "log", "message": line.strip()})
        await asyncio.sleep(0.01) # Tránh nghẽn event loop

    process.wait()
    await websocket.send_json({"type": "status", "message": "DONE!"})
```
