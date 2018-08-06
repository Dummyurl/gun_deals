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
                    <label class="control-label">IDs</label>
                    <input type="text" value="{{ \Request::get("search_id") }}" class="form-control" name="search_id" />                     
                </div> 
                <div class="col-md-4">
                    <label class="control-label">City</label>
                    <input type="text" value="{{ \Request::get("search_city") }}" class="form-control" name="search_city" />                     
                </div>
                <div class="col-md-4">
                    <label class="control-label">State</label>
                    {!! Form::select('search_state', [''=>'Search State'] + $stateList, Request::get("search_state"), ['class' => 'form-control']) !!}                                      
                </div>
                 <div class="col-md-4">
                    <label class="control-label">Country</label>
                    {!! Form::select('search_country', [''=>'Search Country'] + $countryList, Request::get("search_country"), ['class' => 'form-control']) !!}                                      
                </div>
                
                <div class="row">
                    <input type="hidden" name="record_per_page" id="record_per_page"/>
                    <input type="submit" class="btn blue mTop25" value="Search"/>
                    &nbsp;
                    <a href="{{ $list_url }}" class="btn red mTop25">Reset</a>                 
                </div>   
            </div>
                                                
        </form>
    </div>    
</div>    