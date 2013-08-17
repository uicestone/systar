<div class="navbar">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a href="#" class="brand">
                <small>
                    <i class="icon-leaf"></i>
                    Ace Admin
                </small>
            </a><!--/.brand-->
            <ul class="nav ace-nav pull-right">

            <?if($this->user->isLogged()){?>
                <li class="green">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon-envelope"></i>
                        <span class="badge badge-success"><?=$this->message->getNewMessages()?></span>
                    </a>
                </li>
                <li class="green">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            
                        <a href="#profile"><?=$this->user->name?></a>
                    </a>
                </li>
				<li>
	                <a href="mailto:uicestone@gmail.com" title="请提出您宝贵的意见">意见反馈</a>
				</li>
                <li>
                    <a href="/logout">退出</a>
                </li>
            <?}else{?>
                <li class="green">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon-envelope"></i>
                        <a href="/login">登陆</a>
                        <!-- <span class="badge badge-success">5</span> -->
                    </a>
                </li>
            <?}?>
            </ul>
            <!--/.ace-nav-->
        </div><!--/.container-fluid-->
    </div><!--/.navbar-inner-->
</div>
<div class="main-container container-fluid">