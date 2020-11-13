<?php
namespace Application\Entity;

class ImageFilter extends Image {

	protected $stringSearch;
	protected $pageNumber;
	protected $itensPerPage;

	public function getStringSearch() {
		return $this->stringSearch;
	}

	public function setStringSearch($str) {
		$this->stringSearch = $str;
	}

	public function getPageNumber() {
		return $this->pageNumber;
	}

	public function setPageNumber($page) {
		$this->pageNumber = $page;
	}

	public function getItensPerPage() {
		return $this->itensPerPage;
	}

	public function setItensPerPage($itensPerPage) {
		$this->itensPerPage = $itensPerPage;
	}
}