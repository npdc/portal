<?php
\npdc\view\Base::checkUnpublished();
?><div class="overlay"><p>Welcome to the editor pages of the NPDC. We recommend to add content in the order below. This provides the most easy workflow. After submitting a project, dataset or publication the record is reviewed by the NPDC to make sure the record meets the technical standards.</p>

<p>Please provide all information in English to make information accessible to as many people as possible.</p>

<div class="cols">
	<div>
		<h3>Projects</h3>
		<p>General description of what you are going to do or have done. Most information can be taken from the project proposal.</p>
		<button onclick="openUrl('<?=BASE_URL?>/project/new')" class="add">Add new project</button>
		<button onclick="openUrl('<?=BASE_URL?>/project?formid=projectlist&editorOptions[]=edit')" class="edit">Edit my projects</button>
	</div>
	<div>
		<h3>Datasets</h3>
		<p>Detailed description of what you have done and where to find the data.</p>
		<button onclick="openUrl('<?=BASE_URL?>/dataset/new')" class="add">Add new dataset</button>
		<button onclick="openUrl('<?=BASE_URL?>/dataset?formid=datasetlist&editorOptions[]=edit')" class="edit">Edit my datasets</button>
	</div>
	<div>
		<h3>Publications</h3>
		<p>(link to) a publication</p>
		<p>You can provide a DOI to get most fields filled automatically</p>
		<input type="text" name="doi" id="doi" placeholder="DOI of new publication (optional)"/>
		<button onclick="openUrl('<?=BASE_URL?>/publication/new?doi='+$('#doi').val())" class="add">Add new publication</button>
		<button onclick="openUrl('<?=BASE_URL?>/publication?formid=publicationlist&editorOptions[]=edit')" class="edit">Edit my publications</button>
	</div>
</div>

<?php if(!$hideFoot){?>
<div class="foot">
<button onclick="window.parent.closeOverlay();">Return to page</button> <?=$pageTitle?> <?php if(!is_null($editUrl)) { echo '<button onclick="openUrl(\''.$editUrl.'\')">Edit current page</button>';}?>
</div>
<?php } ?>
</div>