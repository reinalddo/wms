const app_recepcion = new Vue({
  el: '#app_recepcion',
  data: {
    titulo: "Recepcion de Materiales",
    check_OC: {
      box: true,
      name: "Orden de Compra"
    },
    check_RL: {
      box: false,
      name: "Recepción Libre"
    },
    check_CD: {
      box: false,
      name: "Cross Docking"
    }
  },
  methods:{
    cambiar (){
      if(this.check_OC.box == true)
      {
        this.check_RL.box = false;
        this.check_CD.box = false;
      }
      if(this.check_RL.box == true)
      {
        this.check_OC.box = false;
        this.check_CD.box = false;
      }
      if(this.check_CD.box == true)
      {
        this.check_OC.box = false;
        this.check_RL.box = false;
      }
    },
  }
})

if($("#checkOC").prop("checked", true)){
   console.log("deam");
   }