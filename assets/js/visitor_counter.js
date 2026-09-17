(function () {
  'use strict';

  function readVisitorConfig() {
    var configElement = document.getElementById('rasamala-visitor-config');
    if (!configElement) {
      return {};
    }

    try {
      return JSON.parse(configElement.textContent || '{}') || {};
    } catch (error) {
      return {};
    }
  }

  function pickRandomQuote(quotes, fallback) {
    var source = Array.isArray(quotes) && quotes.length ? quotes : [fallback];
    return source[Math.floor(Math.random() * source.length)] || fallback;
  }

  var appTarget = document.getElementById('visitor-counter');
  if (!appTarget || !window.Vue || !window.axios) {
    return;
  }

  var config = readVisitorConfig();
  var defaultImage = config.defaultImage || './images/persons/photo.png';
  var fallbackMessage = config.failureMessage || 'Check in failed';
  var reducedMotionMedia = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
  var feedbackResetDelay = Math.max(800, Number(config.feedbackResetDelay || 5000));
  var quickFeedbackResetDelay = Math.max(800, Number(config.quickFeedbackResetDelay || 1800));
  var otherInstitutionValue = String(config.otherInstitutionValue || '');
  var fallbackQuote = config.quoteFallback || {
    content: 'Sing penting madhiang.',
    author: 'Pai-Jo'
  };

  function prefersReducedMotion() {
    return reducedMotionMedia && reducedMotionMedia.matches;
  }

  window.Vue.createApp({
    data: function () {
      return {
        memberId: '',
        visitorName: '',
        institution: '',
        textInfo: '',
        textInfoType: 'info',
        isSubmitting: false,
        submitLabel: config.submitLabel || 'Check In',
        submittingLabel: config.submittingLabel || 'Checking in...',
        image: defaultImage,
        quotesEnabled: config.quotesEnabled !== false,
        quoteFallback: fallbackQuote,
        localQuotes: Array.isArray(config.localQuotes) ? config.localQuotes : [],
        quotes: fallbackQuote,
        activeTab: 'member',
        selectInstitution: '',
        manualInstitution: '',
        otherInstitutionValue: otherInstitutionValue,
        currentTime: '',
        clockInterval: null,
        motionUnsubscribe: null,
        timeout: null,
        csrfName: config.csrfName || '',
        csrfToken: config.csrfToken || '',
        visitorUrl: config.visitorUrl || 'index.php?p=visitor',
        voiceEnabled: config.voiceEnabled === true,
        speechLang: config.speechLang || 'id-ID'
      };
    },
    watch: {
      selectInstitution: function (value) {
        this.institution = this.isManualInstitutionSelected() ? this.manualInstitution : value;
      },
      manualInstitution: function (value) {
        if (this.isManualInstitutionSelected()) {
          this.institution = value;
        }
      },
      activeTab: function (value) {
        this.memberId = '';
        this.visitorName = '';
        this.institution = '';
        this.selectInstitution = '';
        this.manualInstitution = '';
        this.focusCurrentInput(value);
      }
    },
    mounted: function () {
      this.focusCurrentInput();
      this.updateTime();
      this.startClock();

      if (window.RasamalaMotionLifecycle && typeof window.RasamalaMotionLifecycle.subscribe === 'function') {
        this.motionUnsubscribe = window.RasamalaMotionLifecycle.subscribe(function (visible) {
          if (visible) {
            this.startClock();
          } else {
            this.stopClock();
          }
        }.bind(this));
      }

      if (this.quotesEnabled) {
        this.getQuotes();
      }

      document.addEventListener('click', this.keepScannerInputFocused);
    },
    beforeUnmount: function () {
      this.stopClock();
      clearTimeout(this.timeout);
      if (typeof this.motionUnsubscribe === 'function') {
        this.motionUnsubscribe();
        this.motionUnsubscribe = null;
      }
      document.removeEventListener('click', this.keepScannerInputFocused);
    },
    methods: {
      startClock: function () {
        this.stopClock();
        if (document.hidden) return;
        this.clockInterval = window.setInterval(function () {
          this.updateTime();
        }.bind(this), 1000);
      },
      stopClock: function () {
        if (this.clockInterval !== null) {
          window.clearInterval(this.clockInterval);
          this.clockInterval = null;
        }
      },
      focusCurrentInput: function (tabName) {
        var targetTab = tabName || this.activeTab;

        this.$nextTick(function () {
          if (targetTab === 'member' && this.$refs.memberId) {
            this.$refs.memberId.focus();
          } else if (targetTab === 'non-member' && this.$refs.nonMemberNameInput) {
            this.$refs.nonMemberNameInput.focus();
          }
        }.bind(this));
      },
      keepScannerInputFocused: function (event) {
        if (this.textInfo !== '') {
          return;
        }

        if (event.target.closest('input, select, button, .tab-link')) {
          return;
        }

        this.focusCurrentInput();
      },
      updateTime: function () {
        var now = new Date();
        this.currentTime = now.toTimeString().split(' ')[0];
      },
      onImageError: function () {
        this.image = defaultImage;
      },
      getQuotes: function () {
        if (!this.quotesEnabled) {
          this.quotes = this.quoteFallback;
          this.textInfo = '';
          this.textInfoType = 'info';
          return;
        }

        this.quotes = pickRandomQuote(this.localQuotes, this.quoteFallback);
        this.textInfo = '';
        this.textInfoType = 'info';
      },
      plainText: function (message) {
        return String(message || '').replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
      },
      responseType: function (response) {
        var type = response && response.type ? String(response.type).toLowerCase() : '';

        if (['success', 'info', 'warning', 'danger'].indexOf(type) !== -1) {
          return type;
        }

        if (type === 'error') {
          return 'danger';
        }

        return response && response.status === false ? 'danger' : 'info';
      },
      safeImageName: function (image) {
        return String(image || 'photo.png').replace(/[^a-zA-Z0-9._-]/g, '') || 'photo.png';
      },
      currentIdentity: function () {
        return this.activeTab === 'non-member' ? this.visitorName : this.memberId;
      },
      feedbackDelay: function () {
        return prefersReducedMotion() ? quickFeedbackResetDelay : feedbackResetDelay;
      },
      isManualInstitutionSelected: function () {
        return this.otherInstitutionValue !== '' && this.selectInstitution === this.otherInstitutionValue;
      },
      onSubmit: function () {
        var identity = this.currentIdentity();

        if (identity === '' || this.isSubmitting) {
          this.resetForm();
          return;
        }

        this.isSubmitting = true;

        var formData = new FormData();
        formData.append('memberID', identity);
        formData.append('institution', this.institution);
        formData.append('counter', 1);

        if (this.csrfName) {
          formData.append(this.csrfName, this.csrfToken);
        }

        window.axios({
          url: this.visitorUrl,
          method: 'post',
          data: formData,
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(function (response) {
            var responseData = response && response.data && typeof response.data === 'object' ? response.data : {};

            this.textInfo = this.plainText(responseData.message || fallbackMessage);
            this.textInfoType = this.responseType(responseData);
            this.image = './images/persons/' + this.safeImageName(responseData.image);

            if (responseData.new_token) {
              this.csrfToken = responseData.new_token;
            }

            if (this.voiceEnabled) {
              this.textToSpeech(this.textInfo);
            }
          }.bind(this))
          .catch(function (error) {
            var errorData = error && error.response && error.response.data && typeof error.response.data === 'object'
              ? error.response.data
              : {};

            this.textInfo = this.plainText(errorData.message || fallbackMessage);
            this.textInfoType = 'danger';

            if (errorData.new_token) {
              this.csrfToken = errorData.new_token;
            }
          }.bind(this))
          .finally(function () {
            this.isSubmitting = false;
            this.resetForm();
            clearTimeout(this.timeout);
            this.timeout = setTimeout(function () {
              this.getQuotes();
            }.bind(this), this.feedbackDelay());
          }.bind(this));
      },
      resetForm: function () {
        this.memberId = '';
        this.visitorName = '';
        this.institution = '';
        this.selectInstitution = '';
        this.manualInstitution = '';
        this.focusCurrentInput();
      },
      textToSpeech: function (text) {
        if (!('speechSynthesis' in window) || !('SpeechSynthesisUtterance' in window)) {
          return;
        }

        var utterance = new window.SpeechSynthesisUtterance(text);
        utterance.volume = 1;
        utterance.rate = 1;
        utterance.pitch = 1;
        utterance.lang = this.speechLang;
        utterance.voice = null;
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(utterance);
      }
    }
  }).mount('#visitor-counter');
}());
