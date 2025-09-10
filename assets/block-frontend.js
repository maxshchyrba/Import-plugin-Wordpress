(function(){
    'use strict';

    const ajaxUrl = (window.viVacancies && viVacancies.ajax_url) ? viVacancies.ajax_url : '/wp-admin/admin-ajax.php';
    const cf7Shortcode = (window.viVacancies && viVacancies.cf7_shortcode) ? viVacancies.cf7_shortcode : null;

    document.addEventListener('click', function(e){
        const wrapper = e.target.closest('.vi-vacancies-wrapper');
        if(!wrapper) return;

        if(e.target.classList.contains('vi-vac-page') || e.target.classList.contains('vi-filter-apply')){
            e.preventDefault();

            let attrs = {};
            try {
                attrs = JSON.parse(wrapper.dataset.attrs || '{}');
            } catch(e) {
                console.error('Invalid JSON in data-attrs', wrapper.dataset.attrs, e);
            }


           if(e.target.classList.contains('vi-filter-apply')){
                const locationEl = wrapper.querySelector('.vi-filter-location');
                const salaryMinEl = wrapper.querySelector('.vi-filter-salary-min');
                const salaryMaxEl = wrapper.querySelector('.vi-filter-salary-max');
                const perPageEl = wrapper.querySelector('.vi-filter-perpage');
                const sortEl = wrapper.querySelector('.vi-filter-sort');

                attrs.location = locationEl ? locationEl.value : '';
                attrs.salaryMin = salaryMinEl ? salaryMinEl.value : 0;
                attrs.salaryMax = salaryMaxEl ? salaryMaxEl.value : 0;
                attrs.perPage = perPageEl ? perPageEl.value : 10;
                attrs.sort = sortEl ? sortEl.value : 'published_at';
                attrs.paged = 1;
            }
            else {
                attrs.paged = parseInt(e.target.dataset.page) || 1;
            }

            const formData = new FormData();
            formData.append('action','vi_get_vacancies');
            for(const key in attrs){ formData.append(key, attrs[key]); }

         fetch(ajaxUrl, { method:'POST', body:formData })
            .then(r => r.text())
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const newWrapper = temp.querySelector('.vi-vacancies-wrapper');
                if(newWrapper){
                    wrapper.replaceWith(newWrapper);
                }
            })
             .catch(err => console.error('vi_get_vacancies error', err));

        }
    });

    document.addEventListener('click', function(e){
        const btn = e.target.closest('.vi-apply-btn');
        if(!btn) return;

        e.preventDefault();
        const vacancyId = btn.getAttribute('data-vacancy-id') || '';
        const vacancyTitle = btn.getAttribute('data-vacancy-title') || '';

        let modalRoot = document.getElementById('vi-cf7-modal-root');
        if(!modalRoot){
            console.error('CF7 modal root not found. Ensure it is rendered server-side.');
            return;
        }

        modalRoot.style.display = 'flex';
        document.body.classList.add('vi-cf7-modal-open');

        const cf7Form = modalRoot.querySelector('form.wpcf7-form');
        if(!cf7Form){
            console.error('CF7 form not found in modal.');
            return;
        }

        ['vacancy_id','vacancy_title'].forEach(name=>{
            let input = cf7Form.querySelector(`input[name="${name}"]`);
            if(!input){
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                cf7Form.appendChild(input);
            }
            input.value = (name==='vacancy_id')?vacancyId:vacancyTitle;
        });

        if(typeof wpcf7 !== 'undefined' && wpcf7.initForm){
            wpcf7.initForm(cf7Form);
        }

        const firstInput = cf7Form.querySelector('input[type="text"], input[type="email"], textarea, input[type="tel"]');
        if(firstInput) firstInput.focus();
    });

    function closeModal(){
        const modalRoot = document.getElementById('vi-cf7-modal-root');
        if(modalRoot) modalRoot.style.display = 'none';
        document.body.classList.remove('vi-cf7-modal-open');
    }

    document.addEventListener('click', function(e){
        if(e.target.closest('.vi-cf7-modal-overlay') ||
           e.target.closest('.vi-cf7-modal-close') ||
           e.target.closest('[data-action="close"]')){
            closeModal();
        }
    });

    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') closeModal();
    });


document.addEventListener('wpcf7mailsent', function(ev){
    const form = ev.target;
    const modalRoot = document.getElementById('vi-cf7-modal-root');
    if(!modalRoot) return;

    setTimeout(() => {
        modalRoot.style.display = 'none';
        document.body.classList.remove('vi-cf7-modal-open');

        const keep = ['vacancy_id','vacancy_title','_wpcf7','_wpcf7_unit_tag','_wpcf7_nonce'];
        const elements = form.querySelectorAll('input, textarea, select');
        elements.forEach(el=>{
            const name = el.getAttribute('name') || '';
            if(keep.includes(name) || el.type==='submit') return;
            if(el.tagName.toLowerCase()==='select') el.selectedIndex = 0;
            else el.value = '';
        });

        if(typeof wpcf7 !== 'undefined' && wpcf7.api && wpcf7.refill){
            wpcf7.refill(form);
        }

        const out = form.querySelector('.wpcf7-response-output');
        if(out) out.innerHTML = '';

    }, 2000);
}, false);


    ['wpcf7mailfailed','wpcf7invalid'].forEach(evt=>{
        document.addEventListener(evt, function(ev){
            const form = ev.target;
            const out = form.querySelector('.wpcf7-response-output');
            if(out) out.scrollIntoView({behavior:'smooth', block:'center'});
        }, false);
    });

    function updateSalaryDisplays(wrapper){
        const minSlider = wrapper.querySelector('.vi-filter-salary-min');
        const maxSlider = wrapper.querySelector('.vi-filter-salary-max');
        const minDisplay = wrapper.querySelector('.salary-min-display');
        const maxDisplay = wrapper.querySelector('.salary-max-display');

       function sync() {
        let minVal = parseInt(minSlider.value);
        let maxVal = parseInt(maxSlider.value);
        if(minVal > maxVal) minSlider.value = maxVal;
        if(maxVal < minVal) maxSlider.value = minVal;

        minDisplay.textContent = minSlider.value;
        maxDisplay.textContent = maxSlider.value;

        minSlider.setAttribute('aria-valuenow', minSlider.value);
        minSlider.setAttribute('aria-valuetext', minSlider.value);
        maxSlider.setAttribute('aria-valuenow', maxSlider.value);
        maxSlider.setAttribute('aria-valuetext', maxSlider.value);
    }


        minSlider.addEventListener('input', sync);
        maxSlider.addEventListener('input', sync);
    }

    document.querySelectorAll('.vi-vacancies-wrapper').forEach(updateSalaryDisplays);

})();
