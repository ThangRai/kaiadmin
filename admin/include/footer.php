<style>
.floating-buttons {
  position: fixed;
  bottom: 30px;
  right: 30px;
  display: flex;
  flex-direction: column;
  gap: 15px;
  z-index: 9999;
}

.btn-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  background-color: #0d6efd;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  animation: pulseZoom 2s infinite;
  transition: all 0.3s ease;
}

.btn-circle:hover {
  background-color: #0b5ed7;
  transform: scale(1.15);
}

@keyframes pulseZoom {
  0% {
    box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.6);
  }
  70% {
    box-shadow: 0 0 0 20px rgba(0, 123, 255, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
  }
}
.footer-info {
    text-align: center;
}
.footer {
  margin-top: auto; /* đẩy footer xuống đáy */
}
#scrollTopBtn {
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.4s ease;
}
#scrollTopBtn.visible {
  opacity: 1;
  pointer-events: auto;
}
footer {
    display: block !important;
    visibility: visible !important;
}
</style>

<footer class="footer">
  <div class="footer-info">
    <div class="copyright">
      2024, made with <i class="fa fa-heart heart text-danger"></i> by
      <a href="https://levanthang.ct.ws">Thắng Rai</a>
    </div>

    <!-- Nút nổi góc phải -->
    <div class="floating-buttons">
      <button id="scrollTopBtn" class="btn btn-primary btn-circle" onclick="scrollToTop()" title="Lên đầu">
        <i class="fas fa-arrow-up"></i>
      </button>
      <button class="btn btn-info btn-circle" data-bs-toggle="modal" data-bs-target="#supportModal" title="Hỗ trợ">
        <i class="fas fa-comment-dots"></i>
      </button>
    </div>
  </div>
</footer>

<!-- Modal Hỗ trợ -->
<div class="modal fade" id="supportModal" tabindex="-1" aria-labelledby="supportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="supportForm">
        <div class="modal-header">
          <h5 class="modal-title" id="supportModalLabel">Yêu cầu hỗ trợ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Họ và tên</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input type="text" class="form-control" name="fullname" required placeholder="Nhập họ và tên">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Link website</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-globe"></i></span>
              <input type="url" class="form-control" name="website" placeholder="https://example.com">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Nội dung cần hỗ trợ</label>
            <div class="input-group">
              <!-- <span class="input-group-text"><i class="fas fa-comment"></i></span> -->
              <textarea class="form-control" name="message" rows="4" required placeholder="Mô tả vấn đề của bạn"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Gửi hỗ trợ</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const scrollTopBtn = document.getElementById('scrollTopBtn');

  // Kiểm tra vị trí cuộn để ẩn/hiện nút
  function handleScroll() {
    if (window.scrollY > 100) {
      scrollTopBtn.classList.add('visible');
    } else {
      scrollTopBtn.classList.remove('visible');
    }
  }

  // Gọi ngay khi tải lần đầu
  handleScroll();

  // Lắng nghe khi cuộn
  window.addEventListener('scroll', handleScroll);

  // Cuộn lên đầu
  window.scrollToTop = function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // Gửi hỗ trợ
  document.getElementById("supportForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const fullname = this.fullname.value.trim();
    const website = this.website.value.trim();
    const message = this.message.value.trim();

    const discordMessage = {
      content: `📩 **Yêu cầu hỗ trợ mới**\n👤 Họ tên: ${fullname}\n🌐 Website: ${website || 'Không có'}\n📝 Nội dung: ${message}`
    };

    fetch("https://discord.com/api/webhooks/1377828832123424880/61WpAovifisahn3XCQR7H8JYn-F0--9Bf2inithDSkqfAckhaz-hsft5LsUlZFqbhTBU", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(discordMessage)
    }).then(() => {
      Swal.fire({
        title: 'Thành công!',
        text: 'Đã gửi yêu cầu hỗ trợ thành công!',
        icon: 'success',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
      }).then(() => {
        this.reset();
        const modal = bootstrap.Modal.getInstance(document.getElementById('supportModal'));
        modal.hide();
      });
    }).catch(() => {
      Swal.fire({
        title: 'Lỗi!',
        text: 'Gửi yêu cầu hỗ trợ thất bại. Vui lòng thử lại!',
        icon: 'error',
        confirmButtonColor: '#d33',
        confirmButtonText: 'OK'
      });
    });
  });
});
</script>