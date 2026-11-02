# Movie Review Feature (Gemini Bot)

## 1. Mục đích
Tính năng "Auto Movie Review" tự động hóa quá trình cắt video, gửi lên trang web Gemini (gemini.google.com) để nhờ AI viết kịch bản review kèm theo mốc thời gian (timestamps), sau đó lấy kịch bản trả về và cắt ghép, lồng tiếng (TTS) tự động để ra một video review hoàn chỉnh.

## 2. Luồng hoạt động (Workflow)

1. **Upload & Chia nhỏ video (Chunking)**
   - Người dùng tải lên một video dài (thường là phim).
   - Backend sử dụng `MovieReviewCutter` để cắt video thành các đoạn nhỏ bằng FFmpeg stream copy (`-c copy`). 
   - Nếu dùng tài khoản Gemini Pro, video sẽ cắt thành đoạn 14 phút; tài khoản thường cắt 4 phút để tránh giới hạn file size / thời lượng của Gemini.

2. **Automate Gemini Web (Playwright Bot)**
   - Backend sử dụng Playwright qua lớp `GeminiReviewBot`.
   - Bot khởi động trình duyệt Chrome ẩn danh một nửa (có hiển thị UI để tránh block, nhưng dùng profile người dùng hiện tại ở `%LOCALAPPDATA%\Google\Chrome\User Data`).
   - Bot tự động tải video (từng phần) lên và gõ Prompt: "Hãy review phim này theo phong cách ... kèm theo mốc thời gian".
   - Quét kết quả trả về bằng cách dò xem DOM (`message-content`) ổn định chưa (không còn gõ chữ).

3. **Trả kết quả cho Client**
   - Kết quả kịch bản trả về được ghép lại (nếu có nhiều phần) và gửi qua WebSocket về cho Frontend (`MovieReview.tsx`).
   - Frontend hiển thị văn bản kịch bản cho người dùng, cho phép người dùng tự do chỉnh sửa văn bản và mốc thời gian.

4. **Stitch Video (Cắt ghép & Lồng tiếng)**
   - Khi người dùng bấm "Tạo Video Review", Frontend gọi `/api/movie-review/stitch-video`.
   - Backend dùng `MovieReviewStitcher` để regex các mốc thời gian dạng `01:20 - 02:00`.
   - Cắt riêng từng phần ra định dạng `.ts` (bằng cách re-encode với `-c:v libx264` để đồng bộ hoàn toàn khung hình và thời gian).
   - Nối các file `.ts` lại với nhau bằng `concat` demuxer của FFmpeg để ra 1 video mp4 liền mạch (không bị lệch âm thanh/hình ảnh).
   - (Tùy chọn) Gọi `TTSService` để tạo file `.wav` giọng đọc từ kịch bản, tự xóa bỏ các mốc thời gian khỏi chữ để AI không đọc mốc thời gian lên, và chèn vào video, tắt tiếng gốc.

## 3. Kiến trúc Code

- **Frontend**:
  - `frontend/src/components/MovieReview.tsx`: Giao diện chia 2 bước (Lấy kịch bản -> Chỉnh sửa & Render).
  - Tích hợp WebSocket tương tự các tab khác, dùng chung `task_queue` mechanism.

- **Backend (FastAPI)**:
  - `backend/app/api.py`: Thêm 2 endpoints `/api/movie-review/generate-script` và `/api/movie-review/stitch-video`.
  - `backend/app/services/movie_review_cutter.py`: Logic chia nhỏ file.
  - `backend/app/services/gemini_review_bot.py`: Playwright logic.
  - `backend/app/services/movie_review_stitcher.py`: Logic Regex và cắt nối `.ts` bằng FFmpeg.

## 4. Các điểm cần chú ý khi phát triển tiếp
- **Chrome Lock**: Khi chạy Playwright dùng User Data Dir mặc định, trình duyệt Chrome của hệ thống PHẢI ĐƯỢC ĐÓNG. Nếu không Playwright sẽ báo lỗi "user data directory is already in use". Code đã xử lý try-catch để báo lỗi trực tiếp bằng tiếng Việt cho người dùng.
- **Pathing Playwright**: `playwright` phải được cài trong virtual environment (`venv`). Có thể chạy: `..\venv\Scripts\python.exe -m pip install playwright` và `..\venv\Scripts\python.exe -m playwright install chromium`.
- **Cắt nối (Stitching) `.ts`**: Không dùng `.mp4` để trung chuyển giữa cắt và ghép vì `ffmpeg concat` mp4 dễ sinh ra lỗi lệch A/V (Desync). Dùng `.ts` và re-encode `libx264` để đảm bảo an toàn tuyệt đối.
- **Regex Timestamps**: Hiện hỗ trợ `00:00 - 00:00` hoặc `to` hoặc `đến` hoặc `->`. Cần cẩn thận nếu Gemini sinh ra định dạng quá lạ thì người dùng phải tự sửa bằng tay trước khi bấm "Tạo Video Review".

## 5. Yêu cầu hệ thống
- FFmpeg (đã có từ các luồng trước).
- Python package `playwright`.
- Google Chrome cài trên Windows.
