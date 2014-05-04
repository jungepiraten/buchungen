<?php
$title = "Belege";
include("header.php");

$predefined = (object) array();
if (isset($_REQUEST["_"])) {
	$predefined = json_decode($_REQUEST["_"]);
}
?>
<form action="" method="post" class="form-horizontal" accept-charset="UTF-8" enctype="multipart/form-data">
	<fieldset>
		<div class="control-group">
			<label for="beleg" class="control-label">Beleg-Nummer:</label>
			<p class="controls">
				<input type="text" name="beleg" class="span1" value="<?php echo str_pad($beleg, 4, "0", STR_PAD_LEFT); ?>" />
			</p>
		</div>

		<input type="hidden" name="type" value="" />
		<ul class="nav nav-tabs belegType">
			<li><a href="#barkasse">Barkassenbuch</a></li>
			<li><a href="#rechnung">Rechnung</a></li>
			<li><a href="#erstattung">Erstattung</a></li>
			<li><a href="#eigenbeleg">Eigenbeleg</a></li>
			<li><a href="#lastschrift">Lastschrifteinzug</a></li>
			<li><a href="#vermerk">Vermerk</a></li>
		</ul>

		<div class="control-group toggleInput show-eigenbeleg show-rechnung show-erstattung show-lastschrift">
			<label for="DATUM" class="control-label">Buchungsdatum:</label>
			<div class="controls">
				<input type="date" name="DATUM" value="<?php if (isset($predefined->buchungsDatum)) print($predefined->buchungsDatum); else date("Y-m-d"); ?>" />
			</div>
		</div>

		<div class="control-group toggleInput show-eigenbeleg show-rechnung show-erstattung show-barkasse show-lastschrift">
			<label for="KONTEN-SOLL" class="control-label">Soll-Konten:</label>
			<div class="controls">
				<input type="text" name="KONTEN-SOLL" value="<?php if (isset($predefined->sollKonten)) print($predefined->sollKonten); ?>" />
			</div>
		</div>

		<div class="control-group toggleInput show-barkasse">
			<label for="BETRAG-SOLL" class="control-label">Summe Soll:</label>
			<div class="controls">
				<div class="input-append">
					<input type="text" class="span1" name="BETRAG-SOLL" pattern="[0-9]+,[0-9]{2}" placeholder="0,00" />
					<span class="add-on">EUR</span>
				</div>
			</div>
		</div>

		<div class="control-group toggleInput show-eigenbeleg show-rechnung show-erstattung show-barkasse show-lastschrift">
			<label for="KONTEN-HABEN" class="control-label">Haben-Konten:</label>
			<div class="controls">
				<input type="text" name="KONTEN-HABEN" value="<?php if (isset($predefined->habenKonten)) print($predefined->habenKonten); ?>" />
			</div>
		</div>

		<div class="control-group toggleInput show-barkasse">
			<label for="BETRAG-HABEN" class="control-label">Summe Haben:</label>
			<div class="controls">
				<div class="input-append">
					<input type="text" class="span1" name="BETRAG-HABEN" pattern="[0-9]+,[0-9]{2}" placeholder="0,00" />
					<span class="add-on">EUR</span>
				</div>
			</div>
		</div>

		<div class="control-group toggleInput show-eigenbeleg show-rechnung show-erstattung">
			<label for="BETRAG" class="control-label">Betrag:</label>
			<div class="controls">
				<div class="input-append">
					<input type="text" class="span1" name="BETRAG" pattern="[0-9]+,[0-9]{2}" placeholder="0,00" value="<?php if (isset($predefined->betrag)) print(number_format($predefined->betrag, 2, ",", "")); ?>" />
					<span class="add-on">EUR</span>
				</div>
			</div>
		</div>

		<div class="control-group toggleInput show-eigenbeleg show-rechnung show-erstattung show-lastschrift">
			<label for="BESCHLUSS" class="control-label">Beschluss:</label>
			<div class="controls">
				<input type="text" name="BESCHLUSS" />
			</div>
		</div>

		<div class="control-group toggleInput show-eigenbeleg">
			<label for="VORGANG" class="control-label">Vorgang:</label>
			<div class="controls">
				<input type="text" name="VORGANG" />
			</div>
		</div>

		<div class="control-group toggleInput show-eigenbeleg">
			<label for="EMPFAENGER" class="control-label">Zahlungsempf&auml;nger:</label>
			<div class="controls">
				<input type="text" name="EMPFAENGER" />
			</div>
		</div>

		<div class="control-group toggleInput show-eigenbeleg">
			<label for="BEGRUENDUNG" class="control-label">Begr&uuml;ndung:</label>
			<div class="controls">
				<input type="text" name="BEGRUENDUNG" />
			</div>
		</div>

		<div class="control-group toggleInput show-barkasse">
			<label for="BUCHUNGSKONTO" class="control-label">Buchungskonto:</label>
			<div class="controls">
				<input type="text" name="BUCHUNGSKONTO" />
			</div>
		</div>

		<div class="control-group toggleInput show-barkasse">
			<label for="ZEITRAUM" class="control-label">Zeitraum:</label>
			<div class="controls">
				<input type="date" name="ZEITRAUM_START" /> bis <input type="date" name="ZEITRAUM_ENDE" />
			</div>
		</div>

		<div class="control-group toggleInput show-eigenbeleg show-rechnung show-erstattung show-barkasse">
			<label for="ANLAGEN" class="control-label">Anlagen:</label>
			<div class="controls">
				<input type="text" name="ANLAGEN" class="span1" pattern="[0-9]+" />
				<span class="help-inline">Anzahl der Anlagen</span>
			</div>
		</div>

		<div class="control-group toggleInput show-lastschrift">
			<label for="ticket" class="control-label">Ticketnummer:</label>
			<div class="controls">
				<input type="text" name="ticket" />
			</div>
		</div>

		<div class="control-group toggleInput show-lastschrift">
			<label for="DTAUS" class="control-label">DTAUS-Datei:</label>
			<div class="controls">
				<input type="file" name="DTAUS" />
			</div>
		</div>

		<div class="control-group">
			<label for="ANMERKUNGEN" class="control-label">Anmerkungen:</label>
			<div class="controls">
				<textarea rows="3" cols="20" name="ANMERKUNGEN"></textarea>
			</div>
		</div>

<script type="text/javascript">

$(".belegType a").click(function (e) {
	e.preventDefault();
	var type = $(this).attr("href").substring(1);
	$("input[name=type]").val(type);
	$(this).tab('show');
	$(".toggleInput").hide();
	$(".toggleInput.show-" + type).show();
});
$(".toggleInput").hide();

// Auto-Format BETRAG (N,NN)
$("input[name=BETRAG],input[name=BETRAG-SOLL],input[name=BETRAG-HABEN]").change(function() {
	var betrag = parseFloat($(this).val().replace(",","."));
	$(this).val(isNaN(betrag) ? "" : betrag.toFixed(2).replace(".",","));
});

</script>
		
		<div class="form-actions">
			<button type="submit" class="btn btn-primary" name="upload_vpanel" value="1">Abheften</button>
			<button type="submit" class="btn" name="download" value="1">Herunterladen</button>
		</div>
	</fieldset>
</form>
<?php
include("footer.php");
?>
