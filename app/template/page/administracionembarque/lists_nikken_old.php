cargarTransportes<?php
    $contenedorAlmacen = new \AlmacenP\AlmacenP();
    $almacenes = $contenedorAlmacen->getAll();
$clientes  = new \Clientes\Clientes();
$colonias  = new \Clientes\Clientes();
$cpostales  = new \Clientes\Clientes();
$clientes = $clientes->getAll();
$colonias=$colonias->getColonias();
$cpostales=$cpostales->getCPostales();
//TODO Alberto aqui
?>

<!-- Mainly scripts -->
<script src="/js/jquery-2.1.1.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Peity -->
<script src="/js/plugins/peity/jquery.peity.min.js"></script>

<!-- jqGrid -->
<script src="/js/plugins/jqGrid/i18n/grid.locale-es.js"></script>
<script src="/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/js/inspinia.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/js/plugins/ladda/spin.min.js"></script>
<script src="/js/plugins/ladda/ladda.min.js"></script>
<script src="/js/plugins/ladda/ladda.jquery.min.js"></script>
<script src="/js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/js/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="/js/plugins/bootstrap-wizard/form-wizard.min.js"></script>

<!-- Jquery Validate -->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<link href="/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
<link href="/css/plugins/chosen/chosen.css" rel="stylesheet">
<link href="/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

<!-- Mainly scripts -->
<div class="wrapper wrapper-content  animated" id="list">
    <h3>Visor Áreas de Embarque</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Almacén</label>
                                <select class="form-control chosen-select" name="almacen" id="almacen">
                                    <option value="">Seleccione</option>
                                    <?php if(isset($almacenes) && !empty($almacenes)): ?>
                                        <?php foreach($almacenes as $almacen): ?>
                                            <option value="<?php echo $almacen->clave ?>"><?php echo $almacen->nombre ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ruta</label>
                                <select class="form-control chosen-select" name="ruta" id="ruta" onchange="cargarIslas(); cargarDatosGridPrincipal();">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Área de embarque</label>
                                <select class="form-control chosen-select" name="isla" id="isla" onchange="cargarDatosGridPrincipal()">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cliente</label>
                                <select class="form-control chosen-select" name="cliente" id="cliente">
                                    <option value="">Seleccione</option>
                                    <?php if(!empty($clientes)): ?>
                                        <?php foreach($clientes as $cliente): ?>
                                            <option value="<?php echo $cliente->Cve_Clte ?>"><?php echo $cliente->Cve_Clte."-".$cliente->RazonSocial?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Colonia</label>
                                <select class="form-control chosen-select" name="colonia" id="colonia">
                                    <option value="">Seleccione</option>
                                    <?php if(!empty($colonias)): ?>
                                        <?php foreach($colonias as $colonia): ?>
                                            <?php  if ($colonia['Colonia']!=null): ?>
                                            <option value="<?php echo $colonia['Colonia'] ?>"><?php echo $colonia['Colonia']?></option>
                                          <?php endif ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Código Postal</label>
                                <select class="form-control chosen-select" name="cpostal" id="cpostal">
                                    <option value="">Seleccione</option>
                                    <?php if (!empty($cpostales)): ?>
                                        <?php foreach ($cpostales as $cpostal): ?>
                                            <?php if ($cpostal['CodigoPostal'] != null): ?>
                                                <option
                                                    value="<?php echo $cpostal['CodigoPostal'] ?>"><?php echo $cpostal['CodigoPostal'] ?></option>
                                            <?php endif ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-4" style="margin-bottom: 20px; text-align:right">
                            <label for="email">&#160;&#160;</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar" id="buscar" placeholder="Ingrese el número de pedido">
                                <div class="input-group-btn">
                                    <button onclick="cargarDatosGridPrincipal()" type="submit" class="btn btn-primary" id="buscarP">
                                        <span class="fa fa-search"></span> Buscar
                                    </button>
                                </div>
                            </div>
                        </div> 
                        <div class="col-md-12">
                            <h4 id="folio-embarque"></h4>
                        </div> 
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total de guias</label>
                                <input class="form-control" id="txt_tot_guias" type="text" disabled>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Peso total</label>
                                <input class="form-control" id="txt_tot_peso"type="text" disabled>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total volumen</label>
                                <input class="form-control" id="txt_tot_volumen" type="text" disabled>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total piezas</label>
                                <input class="form-control" id="txt_tot_piezas"type="text" disabled>
                            </div>
                        </div>
                        <div class="text-right col-md-4">
                            <label class="text-right"><input type="checkbox" id="btn-asignar-todo" /> Asignar todo</label>
                        </div>
                    </div><br/>
                    <div class="jqGrid_wrapper">
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div><br/>
                    <div class="portlet light bordered" id="form_wizard_1">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class=" icon-layers font-red"></i>
                                            <span class="caption-subject font-red bold uppercase"> Form Wizard -
                                                <span class="step-title"> Step 1 of 4 </span>
                                            </span>
                            </div>
                            <div class="actions">
                                <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                                    <i class="icon-cloud-upload"></i>
                                </a>
                                <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                                    <i class="icon-wrench"></i>
                                </a>
                                <a class="btn btn-circle btn-icon-only btn-default" href="javascript:;">
                                    <i class="icon-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" action="#" id="submit_form" method="POST">
                                <div class="form-wizard">
                                    <div class="form-body">
                                        <ul class="nav nav-pills nav-justified steps">
                                            <li>
                                                <a href="#tab1" data-toggle="tab" class="step">
                                                    <span class="number"> 1 </span>
                                                                <span class="desc">
                                                                    <i class="fa fa-check"></i> Account Setup </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab2" data-toggle="tab" class="step">
                                                    <span class="number"> 2 </span>
                                                                <span class="desc">
                                                                    <i class="fa fa-check"></i> Profile Setup </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab3" data-toggle="tab" class="step active">
                                                    <span class="number"> 3 </span>
                                                                <span class="desc">
                                                                    <i class="fa fa-check"></i> Billing Setup </span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab4" data-toggle="tab" class="step">
                                                    <span class="number"> 4 </span>
                                                                <span class="desc">
                                                                    <i class="fa fa-check"></i> Confirm </span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div id="bar" class="progress progress-striped" role="progressbar">
                                            <div class="progress-bar progress-bar-success"> </div>
                                        </div>
                                        <div class="tab-content">
                                            <div class="alert alert-danger display-none">
                                                <button class="close" data-dismiss="alert"></button> You have some form errors. Please check below. </div>
                                            <div class="alert alert-success display-none">
                                                <button class="close" data-dismiss="alert"></button> Your form validation is successful! </div>
                                            <div class="tab-pane active" id="tab1">
                                                <h3 class="block">Provide your account details</h3>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Username
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="username" />
                                                        <span class="help-block"> Provide your username </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Password
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="password" class="form-control" name="password" id="submit_form_password" />
                                                        <span class="help-block"> Provide your password. </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Confirm Password
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="password" class="form-control" name="rpassword" />
                                                        <span class="help-block"> Confirm your password </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Email
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="email" />
                                                        <span class="help-block"> Provide your email address </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="tab2">
                                                <h3 class="block">Provide your profile details</h3>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Fullname
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="fullname" />
                                                        <span class="help-block"> Provide your fullname </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Phone Number
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="phone" />
                                                        <span class="help-block"> Provide your phone number </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Gender
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <div class="radio-list">
                                                            <label>
                                                                <input type="radio" name="gender" value="M" data-title="Male" /> Male </label>
                                                            <label>
                                                                <input type="radio" name="gender" value="F" data-title="Female" /> Female </label>
                                                        </div>
                                                        <div id="form_gender_error"> </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Address
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="address" />
                                                        <span class="help-block"> Provide your street address </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">City/Town
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="city" />
                                                        <span class="help-block"> Provide your city or town </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Country</label>
                                                    <div class="col-md-4">
                                                        <select name="country" id="country_list" class="form-control">
                                                            <option value=""></option>
                                                            <option value="AF">Afghanistan</option>
                                                            <option value="AL">Albania</option>
                                                            <option value="DZ">Algeria</option>
                                                            <option value="AS">American Samoa</option>
                                                            <option value="AD">Andorra</option>
                                                            <option value="AO">Angola</option>
                                                            <option value="AI">Anguilla</option>
                                                            <option value="AR">Argentina</option>
                                                            <option value="AM">Armenia</option>
                                                            <option value="AW">Aruba</option>
                                                            <option value="AU">Australia</option>
                                                            <option value="AT">Austria</option>
                                                            <option value="AZ">Azerbaijan</option>
                                                            <option value="BS">Bahamas</option>
                                                            <option value="BH">Bahrain</option>
                                                            <option value="BD">Bangladesh</option>
                                                            <option value="BB">Barbados</option>
                                                            <option value="BY">Belarus</option>
                                                            <option value="BE">Belgium</option>
                                                            <option value="BZ">Belize</option>
                                                            <option value="BJ">Benin</option>
                                                            <option value="BM">Bermuda</option>
                                                            <option value="BT">Bhutan</option>
                                                            <option value="BO">Bolivia</option>
                                                            <option value="BA">Bosnia and Herzegowina</option>
                                                            <option value="BW">Botswana</option>
                                                            <option value="BV">Bouvet Island</option>
                                                            <option value="BR">Brazil</option>
                                                            <option value="IO">British Indian Ocean Territory</option>
                                                            <option value="BN">Brunei Darussalam</option>
                                                            <option value="BG">Bulgaria</option>
                                                            <option value="BF">Burkina Faso</option>
                                                            <option value="BI">Burundi</option>
                                                            <option value="KH">Cambodia</option>
                                                            <option value="CM">Cameroon</option>
                                                            <option value="CA">Canada</option>
                                                            <option value="CV">Cape Verde</option>
                                                            <option value="KY">Cayman Islands</option>
                                                            <option value="CF">Central African Republic</option>
                                                            <option value="TD">Chad</option>
                                                            <option value="CL">Chile</option>
                                                            <option value="CN">China</option>
                                                            <option value="CX">Christmas Island</option>
                                                            <option value="CC">Cocos (Keeling) Islands</option>
                                                            <option value="CO">Colombia</option>
                                                            <option value="KM">Comoros</option>
                                                            <option value="CG">Congo</option>
                                                            <option value="CD">Congo, the Democratic Republic of the</option>
                                                            <option value="CK">Cook Islands</option>
                                                            <option value="CR">Costa Rica</option>
                                                            <option value="CI">Cote d'Ivoire</option>
                                                            <option value="HR">Croatia (Hrvatska)</option>
                                                            <option value="CU">Cuba</option>
                                                            <option value="CY">Cyprus</option>
                                                            <option value="CZ">Czech Republic</option>
                                                            <option value="DK">Denmark</option>
                                                            <option value="DJ">Djibouti</option>
                                                            <option value="DM">Dominica</option>
                                                            <option value="DO">Dominican Republic</option>
                                                            <option value="EC">Ecuador</option>
                                                            <option value="EG">Egypt</option>
                                                            <option value="SV">El Salvador</option>
                                                            <option value="GQ">Equatorial Guinea</option>
                                                            <option value="ER">Eritrea</option>
                                                            <option value="EE">Estonia</option>
                                                            <option value="ET">Ethiopia</option>
                                                            <option value="FK">Falkland Islands (Malvinas)</option>
                                                            <option value="FO">Faroe Islands</option>
                                                            <option value="FJ">Fiji</option>
                                                            <option value="FI">Finland</option>
                                                            <option value="FR">France</option>
                                                            <option value="GF">French Guiana</option>
                                                            <option value="PF">French Polynesia</option>
                                                            <option value="TF">French Southern Territories</option>
                                                            <option value="GA">Gabon</option>
                                                            <option value="GM">Gambia</option>
                                                            <option value="GE">Georgia</option>
                                                            <option value="DE">Germany</option>
                                                            <option value="GH">Ghana</option>
                                                            <option value="GI">Gibraltar</option>
                                                            <option value="GR">Greece</option>
                                                            <option value="GL">Greenland</option>
                                                            <option value="GD">Grenada</option>
                                                            <option value="GP">Guadeloupe</option>
                                                            <option value="GU">Guam</option>
                                                            <option value="GT">Guatemala</option>
                                                            <option value="GN">Guinea</option>
                                                            <option value="GW">Guinea-Bissau</option>
                                                            <option value="GY">Guyana</option>
                                                            <option value="HT">Haiti</option>
                                                            <option value="HM">Heard and Mc Donald Islands</option>
                                                            <option value="VA">Holy See (Vatican City State)</option>
                                                            <option value="HN">Honduras</option>
                                                            <option value="HK">Hong Kong</option>
                                                            <option value="HU">Hungary</option>
                                                            <option value="IS">Iceland</option>
                                                            <option value="IN">India</option>
                                                            <option value="ID">Indonesia</option>
                                                            <option value="IR">Iran (Islamic Republic of)</option>
                                                            <option value="IQ">Iraq</option>
                                                            <option value="IE">Ireland</option>
                                                            <option value="IL">Israel</option>
                                                            <option value="IT">Italy</option>
                                                            <option value="JM">Jamaica</option>
                                                            <option value="JP">Japan</option>
                                                            <option value="JO">Jordan</option>
                                                            <option value="KZ">Kazakhstan</option>
                                                            <option value="KE">Kenya</option>
                                                            <option value="KI">Kiribati</option>
                                                            <option value="KP">Korea, Democratic People's Republic of</option>
                                                            <option value="KR">Korea, Republic of</option>
                                                            <option value="KW">Kuwait</option>
                                                            <option value="KG">Kyrgyzstan</option>
                                                            <option value="LA">Lao People's Democratic Republic</option>
                                                            <option value="LV">Latvia</option>
                                                            <option value="LB">Lebanon</option>
                                                            <option value="LS">Lesotho</option>
                                                            <option value="LR">Liberia</option>
                                                            <option value="LY">Libyan Arab Jamahiriya</option>
                                                            <option value="LI">Liechtenstein</option>
                                                            <option value="LT">Lithuania</option>
                                                            <option value="LU">Luxembourg</option>
                                                            <option value="MO">Macau</option>
                                                            <option value="MK">Macedonia, The Former Yugoslav Republic of</option>
                                                            <option value="MG">Madagascar</option>
                                                            <option value="MW">Malawi</option>
                                                            <option value="MY">Malaysia</option>
                                                            <option value="MV">Maldives</option>
                                                            <option value="ML">Mali</option>
                                                            <option value="MT">Malta</option>
                                                            <option value="MH">Marshall Islands</option>
                                                            <option value="MQ">Martinique</option>
                                                            <option value="MR">Mauritania</option>
                                                            <option value="MU">Mauritius</option>
                                                            <option value="YT">Mayotte</option>
                                                            <option value="MX">Mexico</option>
                                                            <option value="FM">Micronesia, Federated States of</option>
                                                            <option value="MD">Moldova, Republic of</option>
                                                            <option value="MC">Monaco</option>
                                                            <option value="MN">Mongolia</option>
                                                            <option value="MS">Montserrat</option>
                                                            <option value="MA">Morocco</option>
                                                            <option value="MZ">Mozambique</option>
                                                            <option value="MM">Myanmar</option>
                                                            <option value="NA">Namibia</option>
                                                            <option value="NR">Nauru</option>
                                                            <option value="NP">Nepal</option>
                                                            <option value="NL">Netherlands</option>
                                                            <option value="AN">Netherlands Antilles</option>
                                                            <option value="NC">New Caledonia</option>
                                                            <option value="NZ">New Zealand</option>
                                                            <option value="NI">Nicaragua</option>
                                                            <option value="NE">Niger</option>
                                                            <option value="NG">Nigeria</option>
                                                            <option value="NU">Niue</option>
                                                            <option value="NF">Norfolk Island</option>
                                                            <option value="MP">Northern Mariana Islands</option>
                                                            <option value="NO">Norway</option>
                                                            <option value="OM">Oman</option>
                                                            <option value="PK">Pakistan</option>
                                                            <option value="PW">Palau</option>
                                                            <option value="PA">Panama</option>
                                                            <option value="PG">Papua New Guinea</option>
                                                            <option value="PY">Paraguay</option>
                                                            <option value="PE">Peru</option>
                                                            <option value="PH">Philippines</option>
                                                            <option value="PN">Pitcairn</option>
                                                            <option value="PL">Poland</option>
                                                            <option value="PT">Portugal</option>
                                                            <option value="PR">Puerto Rico</option>
                                                            <option value="QA">Qatar</option>
                                                            <option value="RE">Reunion</option>
                                                            <option value="RO">Romania</option>
                                                            <option value="RU">Russian Federation</option>
                                                            <option value="RW">Rwanda</option>
                                                            <option value="KN">Saint Kitts and Nevis</option>
                                                            <option value="LC">Saint LUCIA</option>
                                                            <option value="VC">Saint Vincent and the Grenadines</option>
                                                            <option value="WS">Samoa</option>
                                                            <option value="SM">San Marino</option>
                                                            <option value="ST">Sao Tome and Principe</option>
                                                            <option value="SA">Saudi Arabia</option>
                                                            <option value="SN">Senegal</option>
                                                            <option value="SC">Seychelles</option>
                                                            <option value="SL">Sierra Leone</option>
                                                            <option value="SG">Singapore</option>
                                                            <option value="SK">Slovakia (Slovak Republic)</option>
                                                            <option value="SI">Slovenia</option>
                                                            <option value="SB">Solomon Islands</option>
                                                            <option value="SO">Somalia</option>
                                                            <option value="ZA">South Africa</option>
                                                            <option value="GS">South Georgia and the South Sandwich Islands</option>
                                                            <option value="ES">Spain</option>
                                                            <option value="LK">Sri Lanka</option>
                                                            <option value="SH">St. Helena</option>
                                                            <option value="PM">St. Pierre and Miquelon</option>
                                                            <option value="SD">Sudan</option>
                                                            <option value="SR">Suriname</option>
                                                            <option value="SJ">Svalbard and Jan Mayen Islands</option>
                                                            <option value="SZ">Swaziland</option>
                                                            <option value="SE">Sweden</option>
                                                            <option value="CH">Switzerland</option>
                                                            <option value="SY">Syrian Arab Republic</option>
                                                            <option value="TW">Taiwan, Province of China</option>
                                                            <option value="TJ">Tajikistan</option>
                                                            <option value="TZ">Tanzania, United Republic of</option>
                                                            <option value="TH">Thailand</option>
                                                            <option value="TG">Togo</option>
                                                            <option value="TK">Tokelau</option>
                                                            <option value="TO">Tonga</option>
                                                            <option value="TT">Trinidad and Tobago</option>
                                                            <option value="TN">Tunisia</option>
                                                            <option value="TR">Turkey</option>
                                                            <option value="TM">Turkmenistan</option>
                                                            <option value="TC">Turks and Caicos Islands</option>
                                                            <option value="TV">Tuvalu</option>
                                                            <option value="UG">Uganda</option>
                                                            <option value="UA">Ukraine</option>
                                                            <option value="AE">United Arab Emirates</option>
                                                            <option value="GB">United Kingdom</option>
                                                            <option value="US">United States</option>
                                                            <option value="UM">United States Minor Outlying Islands</option>
                                                            <option value="UY">Uruguay</option>
                                                            <option value="UZ">Uzbekistan</option>
                                                            <option value="VU">Vanuatu</option>
                                                            <option value="VE">Venezuela</option>
                                                            <option value="VN">Viet Nam</option>
                                                            <option value="VG">Virgin Islands (British)</option>
                                                            <option value="VI">Virgin Islands (U.S.)</option>
                                                            <option value="WF">Wallis and Futuna Islands</option>
                                                            <option value="EH">Western Sahara</option>
                                                            <option value="YE">Yemen</option>
                                                            <option value="ZM">Zambia</option>
                                                            <option value="ZW">Zimbabwe</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Remarks</label>
                                                    <div class="col-md-4">
                                                        <textarea class="form-control" rows="3" name="remarks"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="tab3">
                                                <h3 class="block">Provide your billing and credit card details</h3>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Card Holder Name
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="card_name" />
                                                        <span class="help-block"> </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Card Number
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="card_number" />
                                                        <span class="help-block"> </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">CVC
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" placeholder="" class="form-control" name="card_cvc" />
                                                        <span class="help-block"> </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Expiration(MM/YYYY)
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <input type="text" placeholder="MM/YYYY" maxlength="7" class="form-control" name="card_expiry_date" />
                                                        <span class="help-block"> e.g 11/2020 </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Payment Options
                                                        <span class="required"> * </span>
                                                    </label>
                                                    <div class="col-md-4">
                                                        <div class="checkbox-list">
                                                            <label>
                                                                <input type="checkbox" name="payment[]" value="1" data-title="Auto-Pay with this Credit Card." /> Auto-Pay with this Credit Card </label>
                                                            <label>
                                                                <input type="checkbox" name="payment[]" value="2" data-title="Email me monthly billing." /> Email me monthly billing </label>
                                                        </div>
                                                        <div id="form_payment_error"> </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="tab4">
                                                <h3 class="block">Confirm your account</h3>
                                                <h4 class="form-section">Account</h4>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Username:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="username"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Email:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="email"> </p>
                                                    </div>
                                                </div>
                                                <h4 class="form-section">Profile</h4>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Fullname:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="fullname"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Gender:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="gender"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Phone:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="phone"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Address:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="address"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">City/Town:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="city"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Country:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="country"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Remarks:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="remarks"> </p>
                                                    </div>
                                                </div>
                                                <h4 class="form-section">Billing</h4>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Card Holder Name:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="card_name"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Card Number:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="card_number"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">CVC:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="card_cvc"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Expiration:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="card_expiry_date"> </p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Payment Options:</label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static" data-display="payment[]"> </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">
                                                <a href="javascript:;" class="btn default button-previous">
                                                    <i class="fa fa-angle-left"></i> Back </a>
                                                <a href="javascript:;" class="btn btn-outline green button-next"> Continue
                                                    <i class="fa fa-angle-right"></i>
                                                </a>
                                                <a href="javascript:;" class="btn green button-submit"> Submit
                                                    <i class="fa fa-check"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="text-right">
                        <button onclick="embarcar()" type="button" class="btn btn-primary" id="buscarP">
                            <span class="fa fa-search"></span> Embarcar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detalles-cajas" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width:90% !important; max-width:90%">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Lista de Empaque</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="table-responsive">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Folio</th>
                                            <th>Clave</th>
                                            <th>Cliente</th>
                                            <th>Clave Destinatario</th>
                                            <th>Destinatario</th>
                                            <th>Ruta</th>
                                            <th>Total guías</th>
                                            <th>Volumen (m<sup>3</sup>)</th>
                                            <th>Peso (Kg)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="caja_folio"></td>
                                            <td id="caja_clave"></td>
                                            <td id="caja_cliente"></td>
                                            <td id="caja_clave_destinatario"></td>
                                            <td id="caja_destinatario"></td>
                                            <td id="caja_ruta"></td>
                                            <td class="text-right" id="caja_guias"></td>
                                            <td class="text-right" id="caja_volumen"></td>
                                            <td class="text-right" id="caja_peso"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="ibox-content">
                            <div class="jqGrid_wrapper" id="detalle_wrapper">
                                <table id="grid-detalles-caja"></table>
                                <div id="grid-detalles-caja-pager"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detalles-pedido" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="max-width:100%">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Detalles de la guía</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="table-responsive">
                            <div class="col-md-12" style="padding-left: 0px;">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Folio</th>
                                            <th>Cliente</th>
                                            <th>Clave</th>
                                            <th>No. caja</th>
                                            <th>Guía</th>
                                            <th>No. de productos</th>
                                            <th>Volumen (m<sup>3</sup>)</th>
                                            <th>Peso (Kg)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="pedido_folio"></td>
                                            <td id="pedido_cliente"></td>
                                            <td id="pedido_clave"></td>
                                            <td id="pedido_nro"></td>
                                            <td id="pedido_guias"></td>
                                            <td class="text-right" id="pedido_cantidad"></td>
                                            <td class="text-right" id="pedido_volumen"></td>
                                            <td class="text-right" id="pedido_peso"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="table-responsive">
                            <div class="col-md-12" style="padding-left: 0px;">
                                <table id="grid-detalles-pedidos"></table>
                                <div id="grid-pager2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-transporte" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="max-width:100%">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Transportes</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <select class="form-control chosen-select" name="almacen" id="transporte">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button onclick="guardarTransporte()" type="button" class="btn btn-primary" id="buscarP"><span class="fa fa-search"></span> Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-contenedores" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="max-width:100%">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4>Pallet | Contenedores</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <select class="form-control chosen-select" name="almacen" id="contenedor">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <br>
                    <br>
                   <div class="row">
                        <div class="table-responsive">
                            <div class="col-md-12">
                                <table id="detalles" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Acciones</th>
                                            <th>N°. Caja</th>
                                            <th>Clave Caja</th>
                                            <th>Tipo caja</th>
                                            <th>Guia</th>
                                            <th>Folio</th>
                                            <th>Volumen (m<sup>3</sup>)</th>
                                            <th>Peso (Kg)</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button onclick="guardarPallet()" type="button" class="btn btn-primary" id="buscarP"><span class="fa fa-search"></span> Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal_fotos" role="dialog">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center modal-lg" style="width: 95% !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 align="center">Foto de Empaque</h4>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <!--contenido-->
                                <div class="row" align="center">
                                    <form id="elegir1" class="form_img">
                                        <input type="hidden" name="action" value="subirImagen" />
                                        <input type="hidden" name="idPedido" value="" class="idPedido"/>
                                        <input type="hidden" name="numeroImagen" value="1" />
                                        <input type="hidden" name="foto_to_up_1" id="foto_to_up_1" value="">
                                        <input id="up_1" name="file" type="file" class="file_img"/><br />
                                        <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->
                                    </form>
                                    <img class="fotos" src="" width="33%" id="foto1">
                                </div>                                                                   
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Contenido de Fotos Embarque-->
                <div class="modal-header align-center"> 
                    <h4 align="center">Fotos  de Embarque</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="row col-md-12" align="center">
                                <form id="elegir1" class="form_img">
                                    <input type="hidden" name="action" value="subirImagen" />
                                    <input type="hidden" name="idPedido" value="" class="idPedido"/>
                                    <input type="hidden" name="numeroImagen" value="2" />
                                    <input type="hidden" name="foto_to_up_2" id="foto_to_up_2" value="">
                                    <input id="up_1" name="file" type="file" class="file_img"/><br />
                                    <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->
                                </form>
                                <img class="fotos" src="" width="100%" id="foto2">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row col-md-12" align="center">
                                <form id="elegir1" class="form_img">
                                    <input type="hidden" name="action" value="subirImagen" />
                                    <input type="hidden" name="idPedido" value="" class="idPedido"/>
                                    <input type="hidden" name="numeroImagen" value="3" />
                                    <input type="hidden" name="foto_to_up_3" id="foto_to_up_3" value="">
                                    <input id="up_1" name="file" type="file" class="file_img"/><br />
                                    <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->
                                </form>
                                <img class="fotos" src="" width="100%" id="foto3">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row col-md-12" align="center">
                                <form id="elegir1" class="form_img">
                                    <input type="hidden" name="action" value="subirImagen" />
                                    <input type="hidden" name="idPedido" value="" class="idPedido"/>
                                    <input type="hidden" name="numeroImagen" value="4" />
                                    <input type="hidden" name="foto_to_up_4" id="foto_to_up_4" value="">
                                    <input id="up_1" name="file" type="file" class="file_img"/><br />
                                    <!--input type="submit" class="ui blue inline button" value="Subir archivo" /-->
                                </form>
                                <img class="fotos" src="" width="100%" id="foto4">
                            </div>
                        </div>
                    </div>
                </div>             
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    /**
     * @author Ricardo Delgado.
     * Busca y selecciona el almacen predeterminado para el usuario.
     */
    function almacenPrede() 
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                idUser: '<?php echo $_SESSION["id_user"]?>',
                action: 'search_almacen_pre'
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/almacenPredeterminado/index.php',
            success: function(data) {
                if (data.success == true) 
                {
                    $('#almacen').val(data.codigo.clave).trigger("chosen:updated");
                    cargarIslas();
                    cargarRutas();
                    console.log("cargar transporte1");
                    cargarTransportes();
                    cargarcontenedores();
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
      
    function cargarTransportes()
    {
      console.log("Cargando transportes");
       $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'cargarTransportes',
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/administracionembarque/lista/index_nikken.php',
            success: function(data) {
                if (data.status == true) 
                {
                  console.log("trasnportes cargar", data);
                    var html = '<option value="">Seleccione</option>'
                    $.each(data.data, function(key, value) {
                        html += '<option value="' + value['id'] + '">' + value['nombre'] + " "+ value['descripcion'] + '</option>'
                    })
                    $('#transporte').html(html).trigger("chosen:updated");;
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
    
    function cargarcontenedores()
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: '/api/administracionembarque/lista/index_nikken.php',
            data: {
                action: 'traer_contenedores',
            },
            beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
            },
            success: function(data) 
            {
                if (data.status == true) 
                {
                    console.log("entra contendores");
                    var html = '<option value="">Seleccione</option>'
                    $.each(data.data, function(key, value) {
                        console.log("dataa-con", value);
                        html += '<option value="' + value['contenedor'] + '">' + value['descripcion'] + '</option>'
                    })
                    $('#contenedor').html(html).trigger("chosen:updated");;
                }
            },
            error: function(data) 
            {
                console.log("error");
            }
        });
    }

    function cargarIslas() 
    {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/administracionembarque/lista/index_nikken.php',
        data: {
          action: 'cargarIslas',
          almacen: $('#almacen').val(),
          ruta: $("#ruta").val()
        },
        beforeSend: function(x) {if (x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
        },
        success: function(data) {
          if (data.status == true) 
          {
            console.log("cargar islas");
            var html = '<option value="">Seleccione</option>'
            $.each(data.data, function(key, value) {
                html += '<option value="' + value['id'] + '">' + value['descripcion'] + '</option>'
            })
            $('#isla').html(html).trigger("chosen:updated");;
          }
        },
        error: function(res) {
          window.console.log(res);
        }
      });
    }
      
    function cargarRutas() {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'cargarRutas',
                almacen: $('#almacen').val()
            },
            beforeSend: function(x) {
                if (x && x.overrideMimeType) 
                {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: '/api/administracionembarque/lista/index_nikken.php',
            success: function(data) {
                if (data.status == true) 
                {
                    var html = '<option value="">Seleccione</option>';
                    $.each(data.data, function(key, value) {
                        html += '<option value="' + value['clave'] + '">' + value['descripcion'] + '</option>';
                    })
                    $('#ruta').html(html).trigger("chosen:updated");
                }
            },
            error: function(res) {
                window.console.log(res);
            }
        });
    }
  
    function sumadores()//EDG
    {
      var myData = $("#grid-table").jqGrid('getRowData');
      var j = 0;
      var guias = 0, pesos = 0.00, volumen = 0.00, piezas = 0;
      $.each(myData, function(key, value) {
        if(myData[j].total_guias == "" || myData[j].total_guias == null){var data_guias = "0";}else{var data_guias = myData[j].total_guias;}
        if(myData[j].total_peso == "" || myData[j].total_peso == null){var data_peso = "0";}else{var data_peso = myData[j].total_peso;}
        if(myData[j].volumen == "" || myData[j].volumen == null){var data_volumen = "0";}else{var data_volumen = myData[j].volumen;}
        if(myData[j].piezas == "" || myData[j].piezas == null){var data_piezas = "0";}else{var data_piezas = myData[j].piezas;}
        guias = guias + parseFloat(data_guias);
        pesos = pesos + parseFloat(data_peso);
        volumen = volumen + parseFloat(data_volumen);
        piezas = piezas + parseFloat(data_piezas);
        j++;
      })
      $("#txt_tot_guias").val(guias);
      $("#txt_tot_peso").val(pesos);
      $("#txt_tot_volumen").val(volumen);
      $("#txt_tot_piezas").val(piezas);
    }

    $(function($) {
        almacenPrede()

        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function() {
            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() - 60);
        })
            //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid('setGridWidth', $(window).width() - 200);
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '/api/administracionembarque/lista/index_nikken.php',
            datatype: "json",
            shrinkToFit: false,
            height: 'auto',
            postData: {

              action: 'cargarGridPrincipal'
            },
            mtype: 'POST',
            colNames: ["Acción", "Embarcar","Folio","Pedido","Zona Embarque (Isla)", "Clave de Cliente", "Cliente","C. Postal","Clave de destinatario","Destinatario", "Clave de sucursal","Direccion", "Ruta", "Total guías", "Peso total","Volumen","Piezas"],
            colModel: [
                {name: 'myac',index: 'myac',width: 80,align: "center",fixed: true,sortable: false,resize: false,formatter: imgFormatDetallePedido},
                {name: 'embarcar',index: 'embarcar',width: 80,fixed: true,sortable: false,resize: false,align: "center",formatter: "checkbox", formatoptions: {disabled: false},edittype: "checkbox", editoptions: {value: "Yes:No",defaultValue: "Yes"},stype: "select",searchoptions: {sopt: ["eq", "ne"], value: ":Any;true:Yes;false:No"}},
                {name: 'folio',index: 'folio',width: 100,editable: false,sortable: false},
                {name: 'pedido',index: 'pedido',width: 100,editable: false,sortable: false},
                {name: 'isla',index: 'isla',width: 160,editable: false,sortable: false},
                {name: 'cliente',index: 'cliente',width: 200,editable: false,sortable: false},
                {name: 'razon_social',index: 'razon_social',width: 300,editable: false,sortable: false},
                {name: 'cpostal',index: 'cpostal',width: 100,editable: false,sortable: false},
                {name: 'id_destinatario',index: 'id_destinatario', width: 150,editable: false,sortable: false},
                {name: 'destinatario',index: 'destinatario', width: 150,editable: false,sortable: false},
                {name: 'clave_sucursal',index: 'clave_sucursal', width: 150,editable: false,sortable: false},
                {name: 'Direccion_Cliente',index: 'Direccion_Cliente', width: 280,editable: false,sortable: false},
                {name: 'ruta',index: 'ruta', width: 100,editable: false,sortable: false},
                {name: 'total_guias',index: 'total_pedidos',width: 100,editable: false,align: 'right',sortable: false},
                {name: 'total_peso',index: 'total_productos',width: 100,editable: false,align: 'right',sortable: false},
                {name: 'volumen',index: 'volumen',width: 100,editable: false,align: 'right',sortable: false},
                {name: 'piezas',index: 'piezas',width: 100,editable: false,align: 'right',sortable: false}
            ],
            beforeSelectRow: function (rowid, e) {
                var $self = $(this),
                    iCol = $.jgrid.getCellIndex($(e.target).closest("td")[0]),
                    cm = $self.jqGrid("getGridParam", "colModel"),
                    localData = $self.jqGrid("getLocalRow", rowid);
                if (cm[iCol].name === "closed") 
                {
                    localData.closed = $(e.target).is(":checked");
                }
                return true; // allow selection
            },
            rowNum: 10,
            rowList: [10, 30, 40, 50],
            pager: pager_selector,
            viewrecords: true,
            sortorder: "desc",
            autowidth: true,
            loadComplete: sumadores
        });
            
        // Setup buttons
        $(grid_selector).jqGrid('navGrid', '#grid-pager', {edit: false,add: false,del: false,search: false},{height: 200,reloadAfterSubmit: true});

        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size

        function imgFormatDetallePedido(cellvalue, options, rowObject) 
        {
            var embarque    = rowObject[2],
                cliente     = rowObject[5],
                clave       = rowObject[4],
                guias       = rowObject[11],
                peso        = rowObject[12],
                folio       = rowObject[2],
                clave_destinatario = rowObject[6],
                destinatario = rowObject[7],
                ruta         = rowObject[10];
           // console.log(rowObject); EDG
            var html = `<div style="white-space:initial;">
                    <a href="#" onclick="verDetalleDeCajas('`+embarque+`','`+cliente+`','`+clave+`','`+guias+`','`+peso+`','`+clave_destinatario+`','`+destinatario+`','`+ruta+`')">
                      <i class="fa fa-search" alt="Detalle"></i>
                    </a>
                    <a href="#" onclick="printPDF('`+embarque+`')" title="Imprimir PDF">
                      <i class="fa fa-print"></i>PDF
                    </a>
                    <a href="#" onclick="embarqueFoto('`+folio+`')">
                      <i class="fa fa-camera" title="Fotos de embarque"></i>
                    </a>
                </div>`;
            return html;
        }

        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    function fotosEmbarque()
    {
      console.log();
    }
     
    function printPDF(id)
    {
        var title = "Guía de Embarque";
        var cia = <?php echo $_SESSION['cve_cia'] ?>;
        var content = '';
        console.log("PDF1");
        $.ajax({
            url: "/api/administracionembarque/lista/index_nikken.php",
            type: "POST",
            data: {
                "action":"getDataPDF",
                "id": id
            },
            success: function(data, textStatus, xhr){
                console.log("PDF2");
                console.log(data);
                var data = JSON.parse(data);
                var content_wrapper = document.createElement('div');
                /*Encabezado*/
                var table_header = document.createElement('table');
                table_header.style.width = "100%";
                table_header.style.borderSpacing = "0";
                table_header.style.borderCollapse = "collapse";
                var thead_header = document.createElement('thead');
                var tbody_header = document.createElement('tbody');

                var head_content_header = '<tr>'+
                    '<th style="border: 1px solid #ccc; font-size:12px;">Folio</th>' +
                    '<th style="border: 1px solid #ccc; font-size:12px;">Fecha Entrega</th>' +
                    '<th style="border: 1px solid #ccc; font-size:12px;">Destino</th>' +
                    '<th style="border: 1px solid #ccc; font-size:12px;">Comentarios</th>' +
                    '<th style="border: 1px solid #ccc; font-size:12px;">Status</th>' +
                    '<th style="border: 1px solid #ccc; font-size:12px;">Peso</th>' +
                    '<th style="border: 1px solid #ccc; font-size:12px;">Volumen</th>' +
                    '<th style="border: 1px solid #ccc; font-size:12px;">Total Cajas</th>' +
                    '<th style="border: 1px solid #ccc; font-size:12px;">Total Piezas</th>' +
                '</tr>';

                var body_content_header = '<tr>'+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.id+'</td> '+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.fecha_entrega+'</td> '+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.destino+'</td> '+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.comentarios+'</td> '+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.status+'</td> '+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.peso+'</td> '+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.volumen+'</td> '+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.total_cajas+'</td> '+
                    '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+data.header.total_piezas+'</td> '+
                '</tr>';                            
                /*Detalle*/
                var table = document.createElement('table');
                table.style.width = "100%";
                table.style.borderSpacing = "0";
                table.style.borderCollapse = "collapse";
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                var head_content = '<th style="border: 1px solid #ccc; font-size:12px;">Clave</th>'+
                    '<th style="border:1px solid #ccc; font-size:12px;">Descripción</th>'+
                    '<th style="border:1px solid #ccc; font-size:12px;">Cantidad</th>'+
                    '<th style="border:1px solid #ccc; font-size:12px;">Lote</th>    '+
                    '<th style="border:1px solid #ccc; font-size:12px;">Caducidad</th>'+
                    '<th style="border:1px solid #ccc; font-size:12px;">Serie</th>   '+
                    '<th style="border:1px solid #ccc; font-size:12px; width:15%">Costo Promedio</th>   '+
                    '<th style="border:1px solid #ccc; font-size:12px;">Subtotal</th>   '+
                '</tr>';
                var body_content = '';

                data.body.forEach(function(item, index){
                    body_content += '<tr>'+
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: left">'+item.clave+'</td> '+
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: left">'+item.descripcion+'</td> '+
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+item.cantidad+'</td> '+
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+item.lote+'</td> '+
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+item.caducidad+'</td> '+
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+item.serie+'</td> '+
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+item.costoPromedio+'</td> '+
                        '<td style="border: 1px solid #ccc; font-size:12px; white-space:nowrap; text-align: center">'+item.subtotal+'</td> '+
                    '</tr>  ';         
                });
              
                var table_total = document.createElement('table');
                table_total.style.width = "100%";
                table_total.style.borderSpacing = "0";
                table_total.style.borderCollapse = "collapse";
                var tbody_total = document.createElement('tbody');
                var total = '<tr>'+
                    '<th style="border: 1px solid #ccc; font-size:12px;"></th>'+
                    '<th style="border:1px solid #ccc; font-size:12px;"></th>'+
                    '<th style="border:1px solid #ccc; font-size:12px;"></th>'+
                    '<th style="border:1px solid #ccc; font-size:12px;"></th>    '+
                    '<th style="border:1px solid #ccc; font-size:12px;"></th>'+
                    '<th style="border:1px solid #ccc; font-size:12px;"></th>   '+
                    '<th style="border:1px solid #ccc; font-size:12px; width:15%">Total</th>   '+
                    '<th style="border:1px solid #ccc; font-size:12px;">'+data.total+'</th>   '+
                '</tr>' ;
              
                console.log("PDF3");
                tbody_header.innerHTML = body_content_header;
                thead_header.innerHTML = head_content_header;
                table_header.appendChild(thead_header);
                table_header.appendChild(tbody_header);
                tbody.innerHTML = body_content;
                thead.innerHTML = head_content;
                tbody_total.innerHTML = total;
                table.appendChild(thead);
                table.appendChild(tbody);
                table.appendChild(tbody_total);
                content_wrapper.appendChild(table_header);
                content_wrapper.appendChild(document.createElement('br'));
                content_wrapper.appendChild(table);
                content = content_wrapper.innerHTML;
                console.log("PDF4");
                /*Creando formulario para ser enviado*/

                var form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", "/api/reportes/generar/pdf.php");
                form.setAttribute("target", "_blank");
                var input_content = document.createElement('input');
                var input_title = document.createElement('input');
                var input_cia = document.createElement('input');
                input_content.setAttribute('type', 'hidden');
                input_title.setAttribute('type', 'hidden');
                input_cia.setAttribute('type', 'hidden');
                input_content.setAttribute('name', 'content');
                input_title.setAttribute('name', 'title');
                input_cia.setAttribute('name', 'cia');
                input_content.setAttribute('value', content);
                input_title.setAttribute('value', title);
                input_cia.setAttribute('value', cia);
                form.appendChild(input_content);
                form.appendChild(input_title);
                form.appendChild(input_cia);
                document.body.appendChild(form);
                form.submit();
                console.log("PDFn");
            }
        });
    }

    $(function($) {
        var grid_selector = "#grid-detalles-caja";
        var pager_selector = "#grid-detalles-caja-pager";

        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid', function(ev, event_name, collapsed) {
            if (event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed') 
            {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $("#grid-detalles-caja").jqGrid('setGridWidth', $("#modal-detalles-cajas .modal-body").width() - 60);
                }, 0);
            }
        })

        $(grid_selector).jqGrid({
            url: '/api/administracionembarque/lista/index_nikken.php',
            datatype: "local",
            shrinkToFit: false,
            height: 'auto',
            mtype: 'POST',
            colNames: ["Partida","Clave caja","Tipo Caja", "Guía", "Volumen (m3)", "Peso(Kg)", "Acción"],
            colModel: [
                {name: 'nro',index: 'nro',width: 80,editable: false,sortable: false},
                {name: 'clave',index: 'clave',width: 100,editable: false,sortable: false},
                {name: 'tipo',index: 'tipo',width: 150,editable: false,sortable: false},
                {name: 'guia',index: 'guia',width: 248,editable: false,sortable: false},
                {name: 'volumen',index: 'volumen',width: 100,editable: false,align: 'right',sortable: false},
                {name: 'peso',index: 'peso',width: 100,editable: false,align: 'right',sortable: false},
                {name: 'acciones',index: 'acciones',width: 100,editable: false,align: 'center',sortable: false,formatter: imageFormat}
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            viewrecords: true,
        });

        // Setup buttons
        $(grid_selector).jqGrid('navGrid', pager_selector, {edit: false,add: false,del: false,search: false}, {height: 200,reloadAfterSubmit: true});

        function imageFormat(cellvalue, options, rowObject) 
        {
            var nro = rowObject[0];
            var guia = rowObject[3];
            var volumen = rowObject[4];
            var val = "'"+nro+"', '"+guia+"'"+", '"+volumen+"'";
            html = '<a href="#" onclick="cargarGridDetallesPedidos('+val+')"><i class="fa fa-search" title="Detalles"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            return html;
        }

        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });

    $(function($) {
        var grid_selector = "#grid-detalles-pedidos";
        var pager_selector = "#grid-pager2";

        //resize to fit page size
        $(window).on('resize.jqGrid', function() {
            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width() - 2);
        })

        $(grid_selector).jqGrid({
            url: '/api/administracionembarque/lista/index_nikken.php',
            shrinkToFit: false,
            height: 250,
            mtype: 'POST',
            colNames: ["Clave", "Artículo", "Cantidad", "Peso(Kg)"],
            colModel: [
                {name: 'clave',index: 'clave',width: 88,editable: false,sortable: false},
                {name: 'articulo',index: 'articulo',width: 350,editable: false,sortable: false},
                {name: 'cantidad',index: 'cantidad',width: 80,editable: false,align: 'right',sortable: false,},
                {name: 'peso',index: 'peso',width: 120,editable: false,align: 'right',sortable: false}
            ],
            rowNum: 10,
            rowList: [10, 20, 30],
            pager: pager_selector,
            viewrecords: true,
            loadComplete :  function(){
                $('#pedido_cantidad').text($("#grid-detalles-pedidos").getGridParam("reccount")); 
            }
        });

        // Setup buttons
        $(grid_selector).jqGrid('navGrid', '#grid-pager', {edit: false,add: false,del: false,search: false}, {height: 200,reloadAfterSubmit: true});

        $(window).triggerHandler('resize.jqGrid'); //trigger window resize to make the grid get the correct size
        $(document).one('ajaxloadstart.page', function(e) {
            $(grid_selector).jqGrid('GridUnload');
            $('.ui-jqdialog').remove();
        });
    });
</script>
<script>
    $(document).ready(function() {
        cargarDatosGridPrincipal();
        $("#almacen").on('change', function(e) {
            cargarDatosGridPrincipal(e.target.value);
        });

        $(function() {
            $('.chosen-select').chosen();
            $('.chosen-select-deselect').chosen({
                allow_single_deselect: true
            });
        });

        $('#modal-detalles-pedido').on('shown.bs.modal', function (e) {
            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width());
        });

        $('#modal-detalles-cajas').on('shown.bs.modal', function (e) {
            $("#grid-detalles-caja").jqGrid('setGridWidth', $("#modal-detalles-cajas .modal-body").width() - 60);
        });

        $(window).on('resize.jqGrid', function() {
            $("#grid-detalles-pedidos").jqGrid('setGridWidth', $("#modal-detalles-pedido .modal-body").width());
            $("#grid-detalles-caja").jqGrid('setGridWidth', $("#modal-detalles-cajas .modal-body").width() - 60);
        })

        $("#grid-table").jqGrid('setGridWidth', $("#grid-table").parent().width() );
    });
</script>
<script>
    $("#btn-asignar-todo").on('click', function(e){
      console.log("asignar");
        var $checkboxes = $('td[aria-describedby="grid-table_embarcar"] input[type="checkbox"]');
        if(e.target.checked)
        {
            if($checkboxes.length > 0)
            {
                $checkboxes.each(function(i,v){
                    v.checked = true;
                });
            }
        }
        else 
        {
            if($checkboxes.length > 0)
            {
                $checkboxes.each(function(i,v){
                    v.checked = false;
                });
            }
        }
        getCheck();
    });
      
    function cargarDatosGridPrincipal() 
    {
      console.log("Cargar Grid Principal");
      $('#grid-table').jqGrid('clearGridData').jqGrid('setGridParam', {
        postData: {
          almacen: $("#almacen").val(),
          isla: $("#isla").val(),
          texto: $("#buscar").val(),
          ruta: $("#ruta").val(),
          cliente:$("#cliente").val(),
          colonia:$("#colonia").val(),
          cpostal:$("#cpostal").val(),
          action: 'cargarGridPrincipal'
        },
        datatype: 'json'
      }).trigger('reloadGrid', [{
        current: true
      }]);
      /* EDG117 */
//       $.ajax({
//         type: "POST",
//         dataType: "json",
//         url: '/api/administracionembarque/lista/index_nikken.php',
//         data: {
//           action: 'verificarOrdenDeEmbarque',
//           isla :  $("#isla").val(),
//           ruta: $("#ruta").val(),
//           almacen: $("#almacen").val(),
          
//         },
//         success: function(data) {
//           console.log("rsponce",data);
// //           return;
//           infoTemporal.ordenEmbarque = parseInt(data.orden)+1;
//           //$("#folio-embarque").text('Folio de embarque: '+infoTemporal.ordenEmbarque)
         
//           $("#txt_tot_guias").val(data.guias);
//           $("#txt_tot_peso").val(data.peso);
//           $("#txt_tot_volumen").val(data.volumen);
//           $("#txt_tot_piezas").val(data.piezas);
//         },
//       });
//       /* EDG117 */
      
      
      $('#grid-table').on('click', 'input[type="checkbox"]', function() {
        console.log('best', 'click');
        getCheck();
      });   
    }
  
    
      
    setTimeout(function(){cargarDatosGridPrincipal();},1000);
        
    function getCheck()
    {
        var myGrid = $('#grid-table'), i, rowData, folios = [],
            rowIds = myGrid.jqGrid("getDataIDs"),
            n = rowIds.length,
            folios="";
            sessionStorage.folios="";
            if (n==0 || isNaN(n)){
                sessionStorage.folios="";
            }
        for (i = 0; i < n; i++) 
        {
            rowData = myGrid.jqGrid("getRowData", rowIds[i]);
            if (rowData.embarcar=='Yes') 
            {
                //folios+="'"+rowData.folio+"',";
                sessionStorage.folios+="'"+rowData.folio+"',";
            }
        }
       // folios=folios.slice(0,-1);
        sessionStorage.folios = sessionStorage.folios.slice(0,-1);

        console.log(folios);
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                //folios :  folios,
                folios :  sessionStorage.folios,
                action: 'totalesPesosGuias'
            },
            url: '/api/administracionembarque/lista/index_nikken.php',
            success: function(data) {
                //TODO Alberto guardar en sessionStorage para que no se pierdan los datos al paginar

                $("#txt_tot_guias").val(data.guias);
                $("#txt_tot_peso").val(data.peso);
                $("#txt_tot_volumen").val(data.volumen);
                $("#txt_tot_piezas").val(data.piezas);
            },
        });     
    }

    function verDetalleDeCajas(folio, cliente, clave, guias, peso, clave_destinatario, destinatario, ruta) 
    {
      infoTemporal.folio = folio;
      infoTemporal.cliente = cliente;
      infoTemporal.clave = clave;
      infoTemporal.guias = guias;
      infoTemporal.peso = peso;
      infoTemporal.clave_destinatario = clave_destinatario;
      infoTemporal.destinatario = destinatario;
      infoTemporal.ruta = ruta;

      $.ajax({
        type: "POST",
        dataType: "json",
        url: '/api/administracionembarque/lista/index_nikken.php',
        data: {
          action: 'obtenerVolumenCajas',
          folio :  folio,
        },
        success: function(data) {                   
          $('#caja_volumen').text(data.volumen);
        },
      });

      cargarGridDetallesCajas(folio);

      $('#caja_folio').text(folio);
      $('#caja_cliente').text(cliente);
      $('#caja_clave').text(clave);
      $('#caja_guias').text(guias);            
      $('#caja_peso').text(peso);
      $('#caja_clave_destinatario').text(clave_destinatario);
      $('#caja_destinatario').text(destinatario);
      $('#caja_ruta').text(ruta);      
      $("#modal-detalles-cajas").modal('show');
    }
      
    function cargarGridDetallesCajas(folio) 
    {
        $('#grid-detalles-caja').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    folio: folio,
                    action: 'cargarDetalleCajas'
                },
                datatype: 'json'
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
    }

    function cargarGridDetallesPedidos(nro, guia, volumen) 
    {
        $('#grid-detalles-pedidos').jqGrid('clearGridData')
            .jqGrid('setGridParam', {
                postData: {
                    folio: infoTemporal.folio,
                    partida: nro,
                    action: 'cargarDetallePedido'
                },
                datatype: 'json'
            })
            .trigger('reloadGrid', [{
                current: true
            }]);
            
        $('#pedido_nro').text(nro); 
        $('#pedido_folio').text(infoTemporal.folio);
        $('#pedido_cliente').text(infoTemporal.cliente);
        $('#pedido_clave').text(infoTemporal.clave);
        $('#pedido_guias').text(guia); 
        $('#pedido_volumen').text(volumen); 
        $('#pedido_peso').text(infoTemporal.peso);
        $("#modal-detalles-pedido").modal('show');
    }
        
    $("#buscar").keyup(function(event) {
        if (event.keyCode == 13) 
        {
            cargarDatosGridPrincipal()
        }
    });

    function embarcar()
    {
  
        var myGrid = $('#grid-table'),
            i,
            rowData,
            folios = [],
            rowIds = myGrid.jqGrid("getDataIDs"),
            n = rowIds.length;
        for (i = 0; i < n; i++) 
        {
            rowData = myGrid.jqGrid("getRowData", rowIds[i]);
            if (rowData.embarcar=='Yes') 
            {
              console.log("1234");
              folios.push(rowData.pedido);
            }
        }
        swal({
            title: "Embarcar",
            text: "¿Desea Utilizar Pallet | Contenedor?",
            type: "success",

            showCancelButton: true,
            cancelButtonText: "Si",
            cancelButtonColor: "#14960a",

            confirmButtonColor: "#55b9dd",
            confirmButtonText: "No",
            closeOnConfirm: true
        },
        function(e) {
            if (e == true) {
                //NO
                if(folios.length == 0)
                {
                    swal({title: "Error",text: "Seleccionar al menos un pedido",type: "error"});
                }
                else
                {
                    cargarIslas();                        
                    //$('#grid-table').jqGrid('clearGridData');
                    $("#modal-transporte").modal('show');
                    console.log("trasnporte");
                }
            }
            else
            { //SI
                console.log("fol1",folios);
                $("#modal-contenedores").modal('show');
                detalles_caja(folios);
                console.log("contenedores");
            }
       });

     
     
    }
      
    function guardarTransporte()
    {
      var cod = document.getElementById("transporte").value;
      console.log("transporte",cod);

      if (cod=='')
      {
        swal({title: "Error",text: "Selecciona un transporte",type: "error"}); 
      }
      else
      {
        var myGrid = $('#grid-table'),
            i,
            rowData,
            folios = [],
            rowIds = myGrid.jqGrid("getDataIDs"),
            n = rowIds.length;
        console.log("isla",isla);
        for (i = 0; i < n; i++) 
        {
          rowData = myGrid.jqGrid("getRowData", rowIds[i]);
          if (rowData.embarcar=='Yes') 
          {
            folios.push(rowData.pedido);
          }
        }
        $.ajax({
          type: "POST",
          dataType: "json",
          url: '/api/administracionembarque/lista/index_nikken.php',
          data: {
            action: 'embarcar',
            folios :  folios,
            isla: $("#isla").val(),
            almacen: $("#almacen").val(),
//             orden: infoTemporal.ordenEmbarque,
          },
          success: function(data) 
          {                    
            if(data.status == 200)
            {
              $.ajax({
                type: "POST",
                dataType: "json",
                url: '/api/administracionembarque/lista/index_nikken.php',
                data: {
                  action: 'guardarTransporte',
                  transporte: $("#transporte").val(),
//                   orden: infoTemporal.ordenEmbarque,
                },
                success: function(data) 
                {
                  if( data.status == 200 )
                  {
                    //$('#grid-table').jqGrid('clearGridData');
                    cargarDatosGridPrincipal();
                    cargarIslas();
                    $("#modal-transporte").modal('hide');
                    swal({title: "Completado",text: "Orden de embarque creada y transporte asignado con éxito",type: "success"});
                    infoTemporal.ordenEmbarque = null
                  }
                },
              });
            }
          },
        });
      }
    }
    
    function detalles_caja(folios)
    {
        console.log("foliocajas", folios);
        html = '';
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/api/administracionembarque/lista/index_nikken.php",
            data: {
                action: 'detalles',
                folio: folios,
            },
            beforeSend: function(x) {
                if(x && x.overrideMimeType) {x.overrideMimeType("application/json;charset=UTF-8");}
                $('#detalles > tbody').html('<tr><td colspan="8">Cargando datos...</td></tr>');
            },
            success: function(data) 
            {
           
                  console.log(data);
                    $.each(data.rows, function(key, value){
                        html += '<tr>'+
                        '<td align="center" class="acciones"><input type="checkbox"></td>'+
                        '<td align="right">'+value.cell[0]+'</td>'+
                        '<td>'+value.cell[1]+'</td>'+
                        '<td>'+value.cell[2]+'</td>'+
                        '<td>'+value.cell[3]+'</td>'+
                        '<td>'+value.cell[4]+'</td>'+
                        '<td>'+value.cell[5]+'</td>'+
                        '<td>'+value.cell[6]+'</td>'+
                        '</tr>';
                    });
                    $('#detalles > tbody').html(html);
                
            },
            error: function(res)
            {
                window.console.log(res);
            }
        });
        $('#modal_detalles').modal('show');  
    }
  
    var infoTemporal = {};
</script>
<script>
    $(document).ready(function(e){
        $(".form_img").on('submit', function(e){
            e.preventDefault();
            console.log("enviando archivo")
            $.ajax({
                type: 'POST',
                url: '/api/administradorpedidos/lista/index.php',
                data: new FormData(this),
                dataType: "json",
                contentType: false,
                cache: false,
                processData:false,
                success: function(msg){
                    console.log(msg,"#foto_to_up_"+ msg.numeroImagen);
                    $("#foto_to_up_"+ msg.numeroImagen).val(msg.nameFile);
                    $("#foto"+ msg.numeroImagen).attr("src","../to_img.php?img=embarques/"+msg.nameFile);
                }
            });
        });
        //file type validation
        $(".file_img").change(function() {
            var file = this.files[0];
            var imagefile = file.type;
            var match= ["image/jpeg","image/png","image/jpg"];
            if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
            {
                alert('Por favor seleccione una imagen del formato (JPEG/JPG/PNG).');
                $(this).val('');
                return false;
            }
            else
            {
                console.log(this.form);
                $(this.form).submit()
            }
        });
    });
  
    function embarqueFoto(folio)
    {
        console.log(folio);
        $(".idPedido").val(folio);
        console.log("Fotos ajax");
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                id_pedido: folio,
                action: "loadFotos"
            },
            url: '/api/administradorpedidos/update/index.php',
        }).done(
        function(data)
        {
            console.log("mostrar Fotos");
            $modal00 = $("#modal_fotos");
            $modal00.modal('show');
            console.log(data);
            window.resurned_data = data;
            if(data.data[0].foto1!=""){$("#foto1").attr("src","../to_img.php?img=embarques/"+data.data[0].foto1);}
            if(data.data[0].foto2!=""){$("#foto2").attr("src","../to_img.php?img=embarques/"+data.data[0].foto2);}
            if(data.data[0].foto3!=""){$("#foto3").attr("src","../to_img.php?img=embarques/"+data.data[0].foto3);}
            if(data.data[0].foto4!=""){$("#foto4").attr("src","../to_img.php?img=embarques/"+data.data[0].foto4);}
        });
    }
</script>
<style>
    @media (min-width: 768px){
        .modal-dialog {
            width: 700px !important;
        }
    }
    @media (max-width: 450px){
        .modal-dialog {
            width: 400px !important;
        }
    }
    .fotos {
        width: 200px;    
    }
</style>