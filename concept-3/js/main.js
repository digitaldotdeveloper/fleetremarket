/* =====================================================================
   FLEET REMARKETING — CONCEPT 3 · shared behaviour
   ===================================================================== */
(function(){
'use strict';
var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;

/* ---- nav shadow + back to top ------------------------------------ */
var nav   = document.getElementById('nav');
var totop = document.getElementById('totop');
function onScroll(){
  var y = scrollY || document.documentElement.scrollTop;
  if(nav)   nav.classList.toggle('scrolled', y > 12);
  if(totop) totop.classList.toggle('show', y > 560);
}
addEventListener('scroll', onScroll, {passive:true}); onScroll();
if(totop) totop.onclick = function(){ scrollTo({top:0, behavior:'smooth'}); };

/* ---- mobile menu -------------------------------------------------- */
var burger = document.getElementById('burger');
var mm      = document.getElementById('mobileMenu');
if(burger && mm){
  burger.onclick = function(){
    var open = mm.classList.toggle('open');
    burger.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  };
  mm.querySelectorAll('a').forEach(function(a){
    a.addEventListener('click', function(){
      mm.classList.remove('open'); burger.classList.remove('open'); document.body.style.overflow='';
    });
  });
}

/* ---- reveal on scroll (replays up + down) ------------------------- */
var rvs = document.querySelectorAll('.rv');
if(rvs.length){
  var io = new IntersectionObserver(function(es){
    es.forEach(function(e){ e.target.classList.toggle('in', e.isIntersecting); });
  }, {threshold:.14});
  rvs.forEach(function(el){ io.observe(el); });
}

/* ---- count up ----------------------------------------------------- */
var nums = document.querySelectorAll('.n[data-count]');
if(nums.length){
  var cio = new IntersectionObserver(function(es){
    es.forEach(function(e){
      if(!e.isIntersecting) return;
      var el=e.target, end=+el.dataset.count, span=el.querySelector('.v');
      if(!span){ cio.unobserve(el); return; }
      if(reduce){ span.textContent=end; cio.unobserve(el); return; }
      var dur=1400, t0=performance.now();
      (function tick(t){
        var p=Math.min((t-t0)/dur,1), ease=1-Math.pow(1-p,3);
        span.textContent=Math.round(end*ease);
        if(p<1) requestAnimationFrame(tick);
      })(t0);
      cio.unobserve(el);
    });
  }, {threshold:.6});
  nums.forEach(function(el){ cio.observe(el); });
}

/* ---- platform marquee (seamless) --------------------------------- */
var mt = document.getElementById('mqTrack');
if(mt) mt.innerHTML += mt.innerHTML;

/* ---- contact form (demo) ----------------------------------------- */
var cf = document.getElementById('contactForm');
if(cf) cf.addEventListener('submit', function(e){
  e.preventDefault();
  var note = document.getElementById('formNote');
  if(note){ note.textContent='✓ THANKS — YOUR MESSAGE IS NOTED (DEMO FORM)'; note.style.color='var(--azure-2)'; }
  cf.reset();
});

/* =====================================================================
   CINEMATIC FILMSTRIP SLIDER (index only)
   ===================================================================== */
var sc = document.getElementById('showcase');
if(sc){
  var slides = [].slice.call(sc.querySelectorAll('.sc-slide'));
  var thumbs = [].slice.call(sc.querySelectorAll('.sc-thumb'));
  var curEl  = document.getElementById('scCur');
  var totEl  = document.getElementById('scTot');
  var barEl  = sc.querySelector('.sc-count .bar i');
  var n = slides.length, i = 0, timer = null, DUR = 6000;

  if(totEl) totEl.textContent = ('0'+n).slice(-2);
  function pad(x){ return ('0'+(x+1)).slice(-2); }

  function go(next){
    if(next === i) return;
    var prev = i;
    i = (next + n) % n;
    slides.forEach(function(s,idx){
      s.classList.remove('prev');
      if(idx === prev) s.classList.add('prev');   // stays beneath while the new one drops in
      s.classList.toggle('active', idx === i);
    });
    thumbs.forEach(function(t,idx){ t.classList.toggle('active', idx === i); });
    if(curEl) curEl.textContent = pad(i);
    runProgress();
  }

  function runProgress(){
    if(barEl && !reduce){
      barEl.style.transition='none'; barEl.style.transform='translateX(-100%)';
      void barEl.offsetWidth;
      barEl.style.transition='transform '+DUR+'ms linear'; barEl.style.transform='translateX(0)';
    }
    thumbs.forEach(function(t){
      var p=t.querySelector('.tp'); if(!p) return;
      if(t.classList.contains('active') && !reduce){
        p.style.transition='none'; p.style.width='0';
        void p.offsetWidth;
        p.style.transition='width '+DUR+'ms linear'; p.style.width='100%';
      } else { p.style.transition='none'; p.style.width='0'; }
    });
  }

  function next(){ go(i+1); }
  function start(){ if(reduce) return; stop(); timer=setInterval(next, DUR); runProgress(); }
  function stop(){ if(timer){ clearInterval(timer); timer=null; } }

  // click a thumbnail to switch
  thumbs.forEach(function(t,idx){
    t.addEventListener('click', function(){ go(idx); start(); });
  });

  // swipe on touch devices
  var stage = sc.querySelector('.sc-track'), tx0=null;
  if(stage){
    stage.addEventListener('touchstart', function(e){ tx0=e.touches[0].clientX; stop(); }, {passive:true});
    stage.addEventListener('touchend', function(e){
      if(tx0===null) return;
      var d=((e.changedTouches[0]||{}).clientX||tx0)-tx0;
      if(Math.abs(d)>50) go(d<0 ? i+1 : i-1);
      tx0=null; start();
    });
  }

  // pause when tab hidden / on hover
  document.addEventListener('visibilitychange', function(){ document.hidden ? stop() : start(); });
  sc.addEventListener('mouseenter', stop);
  sc.addEventListener('mouseleave', start);

  // init
  slides[0].classList.add('active');
  thumbs[0] && thumbs[0].classList.add('active');
  if(curEl) curEl.textContent='01';
  start();
}
})();
