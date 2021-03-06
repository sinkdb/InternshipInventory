import React from 'react';
import $ from 'jquery';
import classNames from 'classnames';


/*********
 * Terms *
 *********/
var TermBlock = React.createClass({
    getInitialState: function() {
        return ({terms: null, hasError: false, selectedTerm: null, dataError: null, dataLoading: true});
    },
    componentWillMount: function() {
        $.ajax({
            url: 'index.php?module=intern&action=GetAvailableTerms',
            dataType: 'json',
            success: function(data) {
                // If the 'error' property exists, then something went wrong on the server-side
                if('error' in data){
                    console.log('Error key exists.');
                    this.setState({dataLoading: false, dataError: data.error});
                }else{
                    this.setState({terms: data, dataLoading: false, dataError: false});
                }
            }.bind(this),
            error: function(xhr, status, err) {
                console.error(status, err.toString());
                this.setState({dataLoading: false, dataError: 'There was an error loading the Term information. Please contact the site administrators.'});
            }.bind(this)
        });
    },
    setError: function(status){
        this.setState({hasError: status});
    },
    handleChange: function(clickEvent){
        this.setState({selectedTerm: clickEvent.target.childNodes[0].value});
    },
    render: function() {

        // If we have no data, and it's still loading, then return an empty div
        if(this.state.terms === null && this.state.dataLoading === true){
            return (<div></div>);
        }

        var errorNotice = null;
        if(this.state.dataError !== false){
            console.log('Showing error notice!');
            errorNotice = <div style={{marginTop: "1em"}} className="alert alert-danger">
                                <p>{this.state.dataError}</p>
                            </div>
        }

        var fgClasses = classNames({
                        'form-group': true,
                        'has-error': this.state.hasError
                    });

        var termDates = null;
        if(this.state.selectedTerm !== null){
            termDates = this.state.terms[this.state.selectedTerm].startDate + " through " + this.state.terms[this.state.selectedTerm].endDate;
        }else{
            termDates = '';
        }

        var termList = '';
        if(this.state.dataError === false && this.state.tems !== null){
            termList = Object.keys(this.state.terms).map(function(key) {
                            return (
                                <label className="btn btn-default" key={key} onClick={this.handleChange}>
                                    <input type="radio" ref="term" name="term" key={key} value={key} />{this.state.terms[key].description}
                                </label>
                            );
                        }.bind(this))
        }

        return (
            <div className="row">
                {errorNotice}
                <div className="col-sm-12 col-md-5 col-md-push-3">
                    <div className={fgClasses} id="term">
                        <label htmlFor="term" className="control-label">Term</label><br />
                        <div className="btn-group" data-toggle="buttons">
                            {termList}
                        </div>
                    </div>
                </div>
                <div className="col-md-4 col-md-push-2">
                    <span id="helpBlock" className="help-block" style={{marginTop: '32px'}}>{termDates}</span>
                </div>
            </div>

        );
    }
});

export default TermBlock;
