var addDta = new AddDta();

function AddDta(){ 

	window.console.log("Empezando el registro");

	var JSONPRO = {},
		ALMACEN = null,
		SQLS = [],
		JSONDATA = {},
		JSONCAJA ={},
		RES = {};

	//almacen : 

	/*window.onload = function(){
		document.getElementById('link').onclick = function(code){
			var txt = "nonon \n <br />"+
					"jkdjdjdjd";
			window.console.log(txt);
			this.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(txt);
		}
	}*/

	this.getJSONPRO = function(){
		return JSONPRO;
	};

	this.getJSONCAJA = function(){
		return JSONCAJA;
	};

	this.getSQL = function(){
		return SQLS;
	};

	this.getJSONDATA = function(){
		return JSONDATA;
	};

	this.getAlmacen = function(){
		return ALMACEN;
	};

	this.initSave = function(){
		runInsertProdu(0);
	};

	this.initSaveData = function(){
		runInsertData(0);
	};

	this.initSaveCaja = function(){
		runInsertCaja(0);
	};

	this.run = function(){
		fillAlmacens();
	};

	init();

	function init(){ 
		fillData();
	}

	function fillCaja(){
		$.ajax({
		    url: "/json/cajas.json",
		    type: "POST",
		    dataType: "json",
		    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
		    success: function(res) {
		        JSONCAJA = res;
		        window.console.log("Se termino de cargar json Cajas");
		    },
		    error: function(res) {
		        JSONCAJA = JSON.parse(res.responseText);
		        window.console.log("Se termino de cargar json Cajas");
		    }
		});
	}

	function fillDataProductos(){
		$.ajax({
		    url: "/json/productos.json",
		    type: "POST",
		    dataType: "json",
		    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
		    success: function(res) {
		        JSONPRO = res;
		        window.console.log("Se termino de cargar json productos");
		        fillAlmacens();
		    },
		    error: function(res) {
		        JSONPRO = JSON.parse(res.responseText);
		        window.console.log("Se termino de cargar json productos");
		        fillAlmacens();
		    }
		});
	}

	function fillData(){
		$.ajax({
		    url: "/json/data.json",
		    type: "POST",
		    dataType: "json",
		    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
		    success: function(res) {
		        JSONDATA = res;
		        window.console.log("Se termino de cargar json data");
		    },
		    error: function(res) {
		        JSONDATA = JSON.parse(res.responseText);
		        window.console.log("Se termino de cargar json data");
		    }
		});
	}

	function runInsertData(count){

		var next = count + 1;

		if(count < JSONDATA.length){

			var node = JSONDATA[count],
				almacen = searchAlmacen(node["ALMACEN"]),
				cve_arti = node["CLAVE"],
				caducidad = formatData(node["Caducidad"]),
				exist = node["EXISTENCIA"],
				lote = node["Lote"],
				ubi = node["UBICACION"];
				data = {
				 		action: 'insert-data',
		                almacen : almacen,  
						cve_arti : cve_arti, 
						caducidad : caducidad, 
						exist : exist, 
						lote : lote, 
						ubi : ubi
					};

			if(lote && caducidad)
				data.svLo = true;

			if(almacen && cve_arti && exist && ubi){ 

				$.ajax({
				    url: "/api/utileria/insertData.php",
				    type: "POST",
				    dataType: "json",
				     data: data,
				    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
		            },
				    success: function(data) {
				    	data.count = next;
				    	var msj;
				    	if(data.msj != "error")
				    		msj = "Registro (" + next +") de " + JSONDATA.length;
				    	else
				    		msj = "ERROR (" + next +") de " + JSONDATA.length;
				    	window.console.log(msj);
				    	document.getElementById('input-input').value = msj;
				    	SQLS.push(data);
				    	runInsertData(next);
				    },
				    error: function(data) {
				        window.console.log(data);
				    }
				});
			}
			else{
				var msj = "NO Registro (" + next +") de " + JSONDATA.length;
				document.getElementById('input-input').value = msj;
				window.console.log(msj);
				runInsertData(next);
			}
		}
		else{
			window.alert("Se guardaron todos los registros");
			window.console.log("Se guardaron todos los registros");
		}
	}

	function fillDataTable(node, count){

		SQLS.push([
			count,

		]);
	}

	function fillAlmacens(){

		$.ajax({
		    url: "/api/utileria/insertData.php",
		    type: "POST",
		    dataType: "json",
		    data: {
                action: 'enter-view'
            },
		    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
            },
		    success: function(res) {
		        ALMACEN = res.almacens;
		    },
		    error: function(res) {
		        ALMACEN = JSON.parse(res.responseText).almacens;
		    }
		});
	}

	function runInsertProdu(count){

		var next = count + 1;

		if(count < JSONPRO.length){

			var node = JSONPRO[count];
			
			var cve = node["Codigo Item"], // cve_articulo
				descrip = node["Descripcion"], // des_articulo
				almacen = '1', // ID_Proveedor
				peso = node["Peso Contenido"], // peso
				codiBarra = node["Codigo de Barras"], // barras2
				caduca = 'S', // Caduca
				compuesto = 'N', // Compuesto
				activo = '1', // Activo
				cajas_palet = node["cjas.x tendido"], // cajas_palet
				lotes = 'S', // control_lotes
				tipCja = node["Unds.caja Madre"], // tipo_caja
				alto = node["Alto"], // alto 
				fondo = node["Profun- didad"],// fondo
				ancho = node["Ancho"],// ancho
				costo = 0,
				grupo = node["Grupo"],// grupo
				codigo_caja = node["cod_caja"],
				uni_caja = node["unids.caj.hija"];
				
			$.ajax({
			    url: "/api/utileria/insertData.php",
			    type: "POST",
			    dataType: "json",
			     data: {
	                action: 'insert-product',
	                cve : cve,  
					descrip : descrip, 
					almacen : almacen, 
					peso : peso, 
					codiBarra : codiBarra, 
					caduca : caduca, 
					compuesto : compuesto, 
					activo : activo, 
					cajas_palet : cajas_palet, 
					lotes : lotes, 
					tipCja : tipCja, 
					alto : alto, 
					fondo : fondo, 
					ancho : ancho, 
					costo : costo,
					grupo : grupo,
					codigo_caja : codigo_caja,
					uni_caja : uni_caja
	            },
			    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
	            },
			    success: function(data) {
			    	data.count = next;
			    	var msj = "Registro (" + next +") de " + JSONPRO.length;
			    	window.console.log(msj);
			    	SQLS.push(data);
			    	runInsertProdu(next);
			    },
			    error: function(data) {
			        window.console.log(data);
			    }
			});
		}
		else{
			console.log("Se guardaron todos los registros");
		}
	}

	function runInsertCaja(count){

		var next = count + 1;

		if(count < JSONCAJA.length){

			var node = JSONCAJA[count];
			
			var cve = node["CODIGO"],
				descrip = node["CORRUGADO"], 
				alto = formatDim(node["ALTO"]), 
				ancho = formatDim(node["ANCHO"]), 
				fondo = formatDim(node["FONDO"]);
				
			$.ajax({
			    url: "/api/utileria/insertData.php",
			    type: "POST",
			    dataType: "json",
			     data: {
	                action: 'insert-caja',
	                cve : cve,  
					descrip : descrip, 
					alto : alto, 
					ancho : ancho,
					fondo : fondo
	            },
			    beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); }
	            },
			    success: function(data) {
			    	data.count = next;
			    	var msj = "Registro (" + next +") de " + JSONCAJA.length;
			    	window.console.log(msj);
			    	SQLS.push(data);
			    	runInsertCaja(next);
			    },
			    error: function(data) {
			        window.console.log(data);
			    }
			});
		}
		else{
			console.log("Se guardaron todos los registros");
		}
	}

	function formatDim(data){

		var array = data.split(" "),
			cm = parseFloat(array[0]);

		return cm / 0.1;
	}

	function searchAlmacen(name){

		for(var i = 0; i < ALMACEN.length; i++){
			if(ALMACEN[i].nombre === name)
				return ALMACEN[i].id;
		}
		return false;
	}

	function formatData(data){

		var array = data.split(" ");

		if(array.length === 3){ 

			var day = array[0],
				_month = array[1],
				year = array[2],
				month = "12";

			if(month === "Ene") month = "01";
			else if(month === "Feb") month = "02";
			else if(month === "Mar") month = "03";
			else if(month === "Abr") month = "04";
			else if(month === "May") month = "05";
			else if(month === "Jun") month = "06";
			else if(month === "Jul") month = "07";
			else if(month === "Ago") month = "08";
			else if(month === "Sep") month = "09";
			else if(month === "Oct") month = "10";
			else if(month === "Nom") month = "11";

			return day + "-" + month + "-" + year;
		}
		else{
			return "";
		}
	}

	function fillAlmacen(){
	}
}
