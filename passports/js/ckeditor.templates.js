/**
 * Register a templates definition set for passports.
 */
CKEDITOR.addTemplates('passports', {
  // The templates definitions.
  templates: [
    {
      title: 'Application process',
      description: 'A list of application process steps',
      html: '<ul class="application-process">' +
        '<li class="step-online">Online</li>' +
        '<li class="step-citizenship">Citizenship</li>' +
        '<li class="step-photo">Photo</li>' +
        '<li class="step-guarantor">Guarantor</li>' +
        '<li class="step-aust-post">Australia Post</li>' +
        '<li class="step-delivery">Delivery</li>' +
        '</ul>'
    }
  ]
});
