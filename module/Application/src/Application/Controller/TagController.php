<?php
namespace Application\Controller;

class TagController extends AbstractRestfulJsonController {
	
	public function getList() {
		$tags = $this->getRepository('Application\Entity\Tag')->findAll();
		if(empty($tags)) {
			$this->getJsonModel()->tags = array();
		}
		else {
			$this->getJsonModel()->tags = $this->arrayLisToJson($tags);
		}
		return $this->getJsonModel();
	}
	
	public function get($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');
		
		$tag = $this->getRepository('Application\Entity\Tag')->find($id);
		if(!$tag) {
			$this->getJsonModel()->tags = array();
		}
		else {
			$this->getJsonModel()->tags = $tag->toArray();
		}
		
		return $this->getJsonModel();
	}
	
	public function create($data) {
		$tag = $this->save($data);
		$this->getJsonModel()->tags = $tag->toArray();
		$this->getJsonModel()->tagCreated = true; 
		return $this->getJsonModel();
	}
	
	public function update($id, $data) {
		$tag = $this->save($data, $id);
		$this->getJsonModel()->tags = $tag->toArray();
		$this->getJsonModel()->tagUpdated = true;
			
		return $this->getJsonModel();
	}
	
	public function save($data, $id=null) {
		if(empty($data))
			$this->applicationError('Dados não informados');
		
		if(!empty($id)) {
			$tag = $this->getRepository('Application\Entity\Tag')->find($id);
			$tag->setTag($data['tag']);
		} else {
			$tag = new \Application\Entity\Tag();
			$tag->setTag($data['tag']);
		}
		
		$this->getRepository('Application\Entity\Tag')->update($tag);
		return $tag;
	}
	
	public function delete($id) {
		if(empty($id))
			$this->applicationError('Id não informado!');
		
		$tag = $this->getRepository('Application\Entity\Tag')->find($id);
		if(!$tag) {
			$this->applicationError('Id não encontrado!');
		}
		
		$this->getRepository('Application\Entity\Tag')->delete($tag);
		
		$this->getJsonModel()->tags = array(
			'deleted' => 'true',
			$tag->toArray()
		);
		return $this->getJsonModel();
	}
	
}