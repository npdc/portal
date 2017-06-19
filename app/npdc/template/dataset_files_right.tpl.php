<h4>Dataset progress</h4>
<p><?=$this->data['dataset_progress']?></p>

<h4>Data quality</h4>
<p><?=$this->data['quality']?></p>

<h4>Access constraints</h4>
<p><?=$this->data['access_constraints']?></p>

<h4>Use constraints</h4>
<p><?=$this->data['use_constraints']?></p>

<a href="<?=BASE_URL.'/'.implode('/', array_slice($this->args, 0, -1))?>">Full dataset description</a>