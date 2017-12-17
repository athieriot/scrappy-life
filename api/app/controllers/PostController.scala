package controllers

import javax.inject._

import models.{PostRepository, PostResponse, PostsResponse}
import play.api.libs.json.Json
import play.api.mvc._
import reactivemongo.bson.BSONObjectID

import scala.concurrent.ExecutionContext

// Api
//TODO: Swagger Spec
//TODO: Search
//TODO: Oid not visible on the outside
//TODO: CORS headers?

// Cli
//TODO: Better Scrapper (Page Number Woot !)
//TODO: PHPDocs

// UI
//TODO: Frontend
//TODO: Selenium Tests
class PostController @Inject()(cc: ControllerComponents, repository: PostRepository, implicit val ec: ExecutionContext)
  extends InjectedController {

  def posts = Action.async {
    repository.getAll.map(posts => {
      Ok(Json.toJson(PostsResponse(posts, posts.length)))
    })
  }

  def post(id: BSONObjectID) = Action.async { request =>
    repository.getOne(id).map {
      case Some(p) => Ok(Json.toJson(PostResponse(p)))
      case None => NotFound
    }
  }
}