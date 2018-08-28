<div class="portlet box blue">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-search"></i>Advance Search 
        </div>
        <div class="tools">
            <a href="javascript:;" class="expand"> </a>
        </div>                    
    </div>
    <div class="portlet-body" style="display: none">  
        <form id="search-frm">
            <div class="row">
                <div class="col-md-4">
                    <label class="control-label">Created Date Range</label>
                    <div class="input-group input-large date-picker input-daterange" data-date="10/11/2012" data-date-format="mm/dd/yyyy">
                        <input type="text" class="form-control" value="{{ \Request::get("search_start_date") }}" name="search_start_date" id="start_date" placeholder="Start Date">
                        <span class="input-group-addon"> To </span>
                        <input type="text" class="form-control" value="{{ \Request::get("search_end_date") }}" name="search_end_date" id="end_date" placeholder="End Date"> 
                    </div>
                </div> 
            
				<div class="col-md-4">
                    <label class="control-label">Ids</label>
                    <input type="text" value="{{ \Request::get("search_id") }}" class="form-control" name="search_id" />                                 
                </div>   
                <div class="col-md-4">
                    <label class="control-label">Cron Name</label>
                         {!! Form::select('search_name', [''=>'Search Cron Name'] + $search_name, Request::get("search_name"), ['class' => 'form-control']) !!}   
                </div>
            </div>
            <div class="clearfix">&nbsp;</div>
            <div class="row">
                <div class="col-md-4">
                    <label class="control-label">Summary</label>
                    <input type="text" value="{{ \Request::get("search_summary") }}" class="form-control" name="search_summary" />
                </div>
                <div class="col-md-4">
                    <label class="control-label">Machine</label>
                    <input type="text" value="{{ \Request::get("search_machine") }}" class="form-control" name="search_machine" />
                </div>         
                <div>
                    <center>
                        <input type="hidden" name="record_per_page" id="record_per_page"/>
                        <input type="submit" class="btn blue mTop25" value="Search"/>
                        &nbsp;
                        <a href="{{ $list_url }}" class="btn red mTop25">Reset</a> 
                    </center>                        
                </div>    
            </div>
            <div class="clearfix">&nbsp;</div>
            
        </form>
    </div>    
</div>      