<?php

	spl_autoload_register(

		function ($Class) {

            $FoldersToCheck = ['services', 'helpers', 'constants'];

            foreach($FoldersToCheck as $Folder){

                $PathToLoad = dirname(__FILE__)."/../app/$Folder/$Class.php";

                if(file_exists($PathToLoad))
                    require_once $PathToLoad;

            }

		}

	);