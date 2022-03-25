{*
* NOTICE OF LICENSE
*
* The MIT License (MIT)
*
* Copyright (c) 2010 CoinCorner
*
* Permission is hereby granted, free of charge, to any person obtaining a copy of
* this software and associated documentation files (the "Software"), to deal in
* the Software without restriction, including without limitation the rights to use,
* copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
* and to permit persons to whom the Software is furnished to do so, subject
* to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
* WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
* IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
*  @author    CoinCorner <support@CoinCorner.com>
*  @copyright 2019 CoinCorner
*  @license   The MIT License (MIT)
*}

<div class="tab">
  <button class="tablinks" onclick="changeTab(event, 'Information')" id="defaultOpen">{l s='Information' mod='coincorner'}</button>
  <button class="tablinks" onclick="changeTab(event, 'Configure Settings')">{l s='Configure Settings' mod='coincorner'}</button>
</div>

<!-- Tab content -->
<div id="Information" class="tabcontent">
	<div class="wrapper">
	  <img src="../modules/coincorner/views/img/gateway.png" style="float:right;"/>
	  <h2 class="coincorner-information-header">
      {l s='Accept Bitcoin on your PrestaShop store with CoinCorner!' mod='coincorner'}
    </h2><br/>
	  <strong>{l s='Who are CoinCorner?' mod='coincorner'}</strong> <br/>
	  <p>
      {l s='We are an Isle of Man based cryptocurrency exchange that has been operating since 2014. We offer a platform for regular businesses to start accepting Bitcoin as a form 
			of payment. ' mod='coincorner'}
    </p><br/>
	  <strong>{l s='Getting started' mod='coincorner'}</strong><br/>
	  <p>
	  	<ul>
				<li>
          {l s='Visit ' mod='coincorner'}<a href="https://www.coincorner.com" target="_blank">{l s='coincorner.com' mod='coincorner'}</a>
          {l s='and register for an account' mod='coincorner'}
         </li>
	  		<li>{l s='Install the CoinCorner module through the PrestaShop Module Manager' mod='coincorner'}</li>
	  		<li>{l s='Get your API credentials and UserId and paste them into the required input fields on the configuration page for the CoinCorner module' mod='coincorner'}</li>
	  		<li>{l s='Read our ' mod='coincorner'}
          <a href="https://www.coincorner.com/pluginintegration" target="_blank">
            {l s='detailed guide' mod='coincorner'}
          </a> {l s='for assistance' mod='coincorner'}</li>
	  	</ul>
	  </p>
	  
	  <p class="sign-up"><br/>
	  	<a href="https://coincorner.com/merchantregister" class="sign-up-button">{l s='Sign up on CoinCorner' mod='coincorner'}</a>
	  </p><br/>
	  <strong>{l s='Features' mod='coincorner'}</strong>
	  <p>
	  	<ul>
	  		<li>{l s='No price volatility risk - Bitcoin payments are converted instantly to GBP' mod='coincorner'}</li>
	  		<li>{l s='Just 1% fee' mod='coincorner'}</li>
			 <li>{l s='Quick and easy setup' mod='coincorner'}</li>
	  		<li>{l s='GBP payouts' mod='coincorner'}</li>
	  		<li>{l s='No chargebacks - guaranteed!' mod='coincorner'}</li>
				<li>{l s='Global reach' mod='coincorner'}</li>
	  	</ul>
	  </p>

	  <p><i>{l s='Questions? Contact support@coincorner.com!' mod='coincorner'}</i></p>
	</div>
</div>

<div id="Configure Settings" class="tabcontent">
  {html_entity_decode($form|escape:'htmlall':'UTF-8')}
</div>

<script>
	document.getElementById("defaultOpen").click();
</script>
