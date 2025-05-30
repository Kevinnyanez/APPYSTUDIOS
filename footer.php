<?php
// Footer unificado y moderno para toda la app
?>
<footer class="footer-appy">
  <div class="footer-logo-wrapper">
    <?php include 'AppyLogoSV.svg'; ?>
  </div>
  <div class="footer-title"><strong>Appy Studios Desarrollo Web</strong></div>
  <div>Aplicaciones y Sitios Webs Profesionales</div>
  <address style="font-style: normal; margin-bottom: 4px;">
    <span>📍 Buenos Aires, Argentina</span> |
    <span>📞 <a href="tel:+542922442186">+2922442186</a></span>
  </address>
  <div>
    📧 <a href="mailto:appystudiosweb@gmail.com">appystudiosweb@gmail.com</a> |
    <a href="https://www.Pronto.com" target="_blank">www.Pronto.com</a>
  </div>
  <div>
    IG: <a href="https://instagram.com/appystudiosweb" target="_blank">@appystudiosweb</a>
  </div>
</footer>
<style>
.footer-appy {
  background-color: #cbd5e1;
  color: #222;
  font-size: 13px;
  width: 100%;
  margin-top: 80px;
  text-align: center;
  border-top: 1px solid #ccc;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  padding: 24px 8px 16px 8px;
}
.footer-logo-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 10px;
  margin-top: 10px;
}
.footer-logo-wrapper svg {
  height: 90px;
  width: auto;
  max-width: 260px;
  display: block;
}
.footer-title {
  font-weight: bold;
  color: #222;
  margin-bottom: 4px;
}
.footer-appy a {
  color: #004080;
  text-decoration: none;
}
.footer-appy a:hover {
  text-decoration: underline;
}
@media (max-width: 600px) {
  .footer-logo-wrapper svg { height: 48px; max-width: 120px; }
  .footer-appy { font-size: 11px; padding: 16px 2px 10px 2px; }
}
</style>
