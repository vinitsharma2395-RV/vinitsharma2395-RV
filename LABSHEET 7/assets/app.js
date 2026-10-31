document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const fields = [...form.querySelectorAll('input, textarea, select')];
      let firstInvalid = null;

      fields.forEach((field) => {
        field.setCustomValidity('');
        if (field.required && !field.value.trim()) {
          field.setCustomValidity('Please fill out this field.');
        } else if (field.type === 'password' && field.minLength > 0 && field.value.length < field.minLength) {
          field.setCustomValidity(`Use at least ${field.minLength} characters.`);
        }
        if (!field.checkValidity() && !firstInvalid) firstInvalid = field;
      });

      if (firstInvalid) {
        event.preventDefault();
        firstInvalid.reportValidity();
        firstInvalid.focus();
      }
    });
  });
});
