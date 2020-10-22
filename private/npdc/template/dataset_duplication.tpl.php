<p>You have requested duplication of the following dataset:</p>
<p style="margin-left:20px;margin-right:20px">&ldquo;<?=$this->model->getCitationString($this->data)?>&rdquo;</p>
<p><strong>Please be aware:</strong></p>
<ul>
    <li>This can't easily be undone! Please only use when you indeed need a duplicate of this dataset.<br/>
        <i>Recommended use is for cases where datasets are mostly similar and only vary in a few fields.</i>
    </li>
    <li>Files (or links to it) will not be transferred (the whole idea of duplicates is that you can easily make a similar description for different files)</li>
    <li>Edits done in the original after creating the duplicate will <strong>not</strong> be transfered to the duplicate, nor the other way</li>
</ul>
<button onclick="openUrl('<?=BASE_URL . '/dataset/' . $this->data['uuid'].'/doduplicate'?>')">Do create a duplicate</button>
<button onclick="openUrl('<?=BASE_URL . '/dataset/' . $this->data['uuid']?>')">Cancel</button>