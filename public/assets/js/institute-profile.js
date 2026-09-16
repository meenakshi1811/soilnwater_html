document.addEventListener('DOMContentLoaded', function () {
  const pageRoot = document.getElementById('instituteProfilePage');
  const form = document.getElementById('instituteEnquiryForm');
  if (!pageRoot || !form) return;

  const enquiryUrl = pageRoot.dataset.enquiryUrl;
  const feedback = document.getElementById('instituteEnquiryFeedback');
  const submitBtn = form.querySelector('.js-institute-enquiry-submit');
  const btnText = submitBtn?.querySelector('.js-enquiry-btn-text');
  const btnSending = submitBtn?.querySelector('.js-enquiry-btn-sending');

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!enquiryUrl || !submitBtn) return;

    submitBtn.disabled = true;
    if (btnText) btnText.classList.add('d-none');
    if (btnSending) btnSending.classList.remove('d-none');
    if (feedback) {
      feedback.classList.add('d-none');
      feedback.classList.remove('alert-success', 'alert-danger');
    }

    try {
      const response = await fetch(enquiryUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: new FormData(form),
      });

      const payload = await response.json().catch(function () { return {}; });

      if (!response.ok) {
        throw new Error(payload.message || 'Unable to send enquiry.');
      }

      if (feedback) {
        feedback.textContent = payload.message || 'Enquiry sent successfully.';
        feedback.classList.remove('d-none');
        feedback.classList.add('alert-success');
      }

      form.reset();
    } catch (error) {
      if (feedback) {
        feedback.textContent = error.message || 'Unable to send enquiry.';
        feedback.classList.remove('d-none');
        feedback.classList.add('alert-danger');
      }
    } finally {
      submitBtn.disabled = false;
      if (btnText) btnText.classList.remove('d-none');
      if (btnSending) btnSending.classList.add('d-none');
    }
  });
});
