//cuộn lên đầu trang
export function scrollToTop() {
  const scrollStep = -window.scrollY / (400 / 5); // 400ms để lên đầu
  const scrollInterval = setInterval(() => {
    if (window.scrollY !== 0) {
      window.scrollBy(0, scrollStep);
    } else {
      clearInterval(scrollInterval);
    }
  }, 15);
}